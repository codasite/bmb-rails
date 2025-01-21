import 'package:bmb_mobile/features/wp_http/wp_urls.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:package_info_plus/package_info_plus.dart';
import 'package:bmb_mobile/core/utils/app_logger.dart';
import 'package:bmb_mobile/features/wp_http/domain/service/wp_http_client.dart';
import 'package:bmb_mobile/core/utils/device_info.dart';
import 'dart:math';

class FcmTokenManager {
  static const String _tokenKey = 'fcm_token';
  final _messaging = FirebaseMessaging.instance;
  final WpHttpClient _client;

  FcmTokenManager(this._client);

  /// Initialize FCM and request permissions
  Future<void> initialize() async {
    await AppLogger.logMessage('Initializing FCM');
    try {
      await AppLogger.logMessage("Requesting FCM permissions");
      await _messaging.requestPermission(
        alert: true,
        badge: true,
        sound: true,
      );

      await AppLogger.logMessage('FCM permissions requested successfully');

      await AppLogger.logMessage('Setting up FCM token refresh listener');
      _messaging.onTokenRefresh.listen(_handleTokenRefresh);
      await AppLogger.logMessage('Finished initializing FCM');
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Failed to initialize FCM'},
      );
    }
  }

  /// Register or update token based on current state
  Future<void> setupToken([String? newToken]) async {
    try {
      await AppLogger.logMessage('Setting up FCM token');

      // Get token from Firebase if not provided
      final token = newToken ?? await _messaging.getToken();
      if (token == null) {
        await AppLogger.logWarning('Failed to get FCM token');
        return;
      }
      await AppLogger.logMessage('Got FCM token: $token');

      await AppLogger.logMessage('Retrieving FCM token from SharedPreferences');
      final prefs = await SharedPreferences.getInstance();
      final oldToken = prefs.getString(_tokenKey);

      if (oldToken != null) {
        await AppLogger.logMessage(
          'Found existing token to update',
          extras: {'old_token': oldToken},
        );
        if (!await _updateToken(oldToken, token)) {
          await AppLogger.logMessage(
              'Token update failed, falling back to registration');
          await _registerToken(token);
        }
      } else {
        await AppLogger.logMessage(
            'No existing token found, registering new token');
        await _registerToken(token);
      }

      await AppLogger.logMessage('Storing token in SharedPreferences');
      await prefs.setString(_tokenKey, token);
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Failed to setup FCM token'},
      );
    }
  }

  /// Handle token refresh by calling setupToken
  Future<void> _handleTokenRefresh(String newToken) async {
    try {
      await AppLogger.logMessage('Handling FCM token refresh');
      await setupToken(newToken);
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Failed to handle token refresh'},
      );
    }
  }

  /// Register a new token with the backend
  Future<bool> _registerToken(String token) async {
    await AppLogger.logMessage('Registering FCM token');
    try {
      final deviceInfo = await _getDeviceInfo();
      final packageInfo = await PackageInfo.fromPlatform();

      await AppLogger.logMessage(
        'Sending registration request',
        extras: {
          'device_id': deviceInfo.id,
          'platform': deviceInfo.platform,
          'app_version': packageInfo.version,
        },
      );

      final response = await _client.post(
        WpUrls.fcmRegisterPath,
        body: {
          'token': token,
          'device_id': deviceInfo.id,
          'platform': deviceInfo.platform,
          'device_name': deviceInfo.name,
          'app_version': packageInfo.version,
        },
      );

      if (response?.statusCode == 201) {
        await AppLogger.logMessage('FCM token registered successfully');
        return true;
      }

      await AppLogger.logWarning(
        'Failed to register FCM token',
        extras: {
          'status_code': response?.statusCode,
          'response': response?.body,
        },
      );
      return false;
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Failed to register FCM token'},
      );
      return false;
    }
  }

  /// Update an existing token
  Future<bool> _updateToken(String oldToken, String newToken) async {
    await AppLogger.logMessage(
      'Updating FCM token',
      extras: {
        'old_token': oldToken,
        'new_token': newToken,
      },
    );
    try {
      final deviceInfo = await _getDeviceInfo();

      await AppLogger.logMessage(
        'Sending token update request',
        extras: {'device_id': deviceInfo.id},
      );

      final response = await _client.put(
        WpUrls.fcmUpdatePath,
        body: {
          'old_token': oldToken,
          'new_token': newToken,
          'device_id': deviceInfo.id,
        },
      );

      if (response?.statusCode == 200) {
        await AppLogger.logMessage('FCM token updated successfully');
        return true;
      }

      await AppLogger.logWarning(
        'Failed to update FCM token',
        extras: {
          'status_code': response?.statusCode,
          'response': response?.body,
        },
      );
      return false;
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Failed to update FCM token'},
      );
      return false;
    }
  }

  /// Deregister the current token
  Future<void> deregisterToken() async {
    await AppLogger.logMessage('Starting FCM token deregistration');
    final prefs = await SharedPreferences.getInstance();
    try {
      final deviceInfo = await _getDeviceInfo();

      await AppLogger.logMessage(
        'Sending deregistration request',
        extras: {'device_id': deviceInfo.id},
      );

      final response = await _client.delete(
        WpUrls.fcmDeregisterPath,
        body: {
          'device_id': deviceInfo.id,
        },
      );

      if (response?.statusCode == 200) {
        await AppLogger.logMessage('Successfully deregistered FCM token');
      } else {
        await AppLogger.logWarning(
          'Failed to deregister FCM token',
          extras: {
            'status_code': response?.statusCode,
            'response': response?.body,
          },
        );
      }
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Failed to deregister FCM token'},
      );
    } finally {
      await AppLogger.logMessage('Removing FCM token from SharedPreferences');
      await prefs.remove(_tokenKey);
    }
  }

  /// Send status update to keep token active
  Future<bool> updateStatus({int maxRetries = 3}) async {
    int attempts = 0;

    Future<bool> attemptUpdate() async {
      try {
        await AppLogger.logMessage(
          'Attempting FCM status update',
          extras: {'attempt': attempts + 1},
        );

        final prefs = await SharedPreferences.getInstance();
        final token = prefs.getString(_tokenKey);

        if (token == null) {
          await AppLogger.logWarning('No FCM token found for status update');
          return false;
        }

        final deviceInfo = await _getDeviceInfo();

        final response = await _client.post(
          WpUrls.fcmStatusPath,
          body: {
            'device_id': deviceInfo.id,
            'status': 'active',
          },
        );

        if (response?.statusCode == 200) {
          await AppLogger.logMessage('FCM status updated successfully');
          return true;
        }

        await AppLogger.logWarning(
          'Failed to update FCM status',
          extras: {
            'status_code': response?.statusCode,
            'response': response?.body,
          },
        );
        return false;
      } catch (e, stackTrace) {
        await AppLogger.logError(
          e,
          stackTrace,
          extras: {
            'message': 'Failed to update FCM status',
            'attempt': attempts + 1,
          },
        );
        return false;
      }
    }

    while (attempts < maxRetries) {
      if (await attemptUpdate()) {
        return true;
      }
      attempts++;

      if (attempts < maxRetries) {
        // Exponential backoff with jitter
        final delay =
            Duration(seconds: (1 << attempts) + (Random().nextInt(3)));
        await AppLogger.logMessage(
          'Retrying FCM status update after delay',
          extras: {
            'attempt': attempts,
            'delay_seconds': delay.inSeconds,
          },
        );
        await Future.delayed(delay);
      }
    }

    if (attempts == maxRetries) {
      await AppLogger.logWarning(
        'FCM status update failed after max retries',
        extras: {'max_retries': maxRetries},
      );
    }

    return false;
  }

  /// Get device information
  Future<DeviceData> _getDeviceInfo() async {
    return await DeviceInfo.getDeviceInfo();
  }
}
