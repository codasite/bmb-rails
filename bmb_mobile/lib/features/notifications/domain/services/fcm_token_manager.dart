import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:device_info_plus/device_info_plus.dart';
import 'package:package_info_plus/package_info_plus.dart';
import 'package:bmb_mobile/core/utils/app_logger.dart';
import 'dart:io';
import '../../../wp_http/wp_urls.dart';
import 'package:bmb_mobile/features/wp_http/domain/service/wp_http_client.dart';

class FcmTokenManager {
  static const String _tokenKey = 'fcm_token';
  final _messaging = FirebaseMessaging.instance;
  final WpHttpClient _client;

  FcmTokenManager(this._client);

  /// Initialize FCM and request permissions
  Future<void> initialize() async {
    try {
      await _messaging.requestPermission(
        alert: true,
        badge: true,
        sound: true,
      );

      await AppLogger.logMessage('FCM permissions requested successfully');

      // Set up token refresh listener
      _messaging.onTokenRefresh.listen(_handleTokenRefresh);
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Failed to initialize FCM'},
      );
    }
  }

  /// Register or update token based on current state
  Future<void> setupToken() async {
    try {
      final token = await _messaging.getToken();
      if (token == null) {
        await AppLogger.logWarning('Failed to get FCM token');
        return;
      }

      final prefs = await SharedPreferences.getInstance();
      final oldToken = prefs.getString(_tokenKey);

      if (oldToken != null) {
        await AppLogger.logMessage('Updating existing FCM token');
        await _updateToken(oldToken, token);
      } else {
        await AppLogger.logMessage('Registering new FCM token');
        await _registerToken(token);
      }

      await prefs.setString(_tokenKey, token);
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Failed to setup FCM token'},
      );
    }
  }

  /// Handle token refresh
  Future<void> _handleTokenRefresh(String newToken) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final oldToken = prefs.getString(_tokenKey);

      if (oldToken != null) {
        await AppLogger.logMessage('Refreshing FCM token');
        await _updateToken(oldToken, newToken);
        await prefs.setString(_tokenKey, newToken);
      }
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
    try {
      final deviceInfo = await _getDeviceInfo();
      final packageInfo = await PackageInfo.fromPlatform();

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
    try {
      final deviceInfo = await _getDeviceInfo();

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
  Future<bool> deregisterToken() async {
    try {
      final deviceInfo = await _getDeviceInfo();
      final prefs = await SharedPreferences.getInstance();

      final response = await _client.delete(
        WpUrls.fcmDeregisterPath,
        body: {
          'device_id': deviceInfo.id,
        },
      );

      if (response?.statusCode == 200) {
        await prefs.remove(_tokenKey);
        await AppLogger.logMessage('FCM token deregistered successfully');
        return true;
      }

      await AppLogger.logWarning(
        'Failed to deregister FCM token',
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
        extras: {'message': 'Failed to deregister FCM token'},
      );
      return false;
    }
  }

  /// Send status update to keep token active
  Future<bool> updateStatus() async {
    try {
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
        extras: {'message': 'Failed to update FCM status'},
      );
      return false;
    }
  }

  /// Get device information
  Future<DeviceData> _getDeviceInfo() async {
    try {
      final deviceInfo = DeviceInfoPlugin();
      if (Platform.isIOS) {
        final iosInfo = await deviceInfo.iosInfo;
        return DeviceData(
          id: iosInfo.identifierForVendor ?? 'unknown',
          platform: 'ios',
          name: '${iosInfo.name} ${iosInfo.model}',
        );
      } else {
        final androidInfo = await deviceInfo.androidInfo;
        return DeviceData(
          id: androidInfo.id,
          platform: 'android',
          name: androidInfo.model,
        );
      }
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Failed to get device info'},
      );
      rethrow;
    }
  }
}

class DeviceData {
  final String id;
  final String platform;
  final String name;

  DeviceData({
    required this.id,
    required this.platform,
    required this.name,
  });
}
