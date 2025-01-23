import 'package:bmb_mobile/features/wp_http/wp_urls.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:package_info_plus/package_info_plus.dart';
import 'package:bmb_mobile/core/utils/app_logger.dart';
import 'package:bmb_mobile/features/wp_http/domain/service/wp_http_client.dart';
import 'package:bmb_mobile/core/utils/device_info.dart';

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

      await AppLogger.logMessage('Setting up initial token');
      await getNewToken();

      await AppLogger.logMessage('Finished initializing FCM');
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Failed to initialize FCM'},
      );
    }
  }

  Future<void> getNewToken() async {
    final token = await _messaging.getToken();
    await AppLogger.logMessage('Got FCM token: $token');
    if (token == null) {
      await AppLogger.logWarning('Failed to get FCM token');
      return;
    }
    await syncToken(token);
  }

  Future<bool> syncToken(String token) async {
    try {
      await AppLogger.logMessage('Syncing FCM token');
      final deviceInfo = await DeviceInfo.getDeviceInfo();
      final packageInfo = await PackageInfo.fromPlatform();

      final response = await _client.post(
        WpUrls.fcmSyncPath,
        body: {
          'token': token,
          'device_id': deviceInfo.id,
          'platform': deviceInfo.platform,
          'device_name': deviceInfo.name,
          'app_version': packageInfo.version,
        },
      );

      if (response?.statusCode == 200 || response?.statusCode == 201) {
        await AppLogger.logMessage('FCM token synced successfully');
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString(_tokenKey, token);
        return true;
      }

      await AppLogger.logWarning(
        'Failed to sync FCM token',
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
        extras: {'message': 'Failed to sync FCM token'},
      );
      return false;
    }
  }

  Future<bool> updateStatus() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString(_tokenKey);

      if (token == null) {
        await AppLogger.logMessage(
            'No FCM token found, attempting to get new token');
        await getNewToken();
        return false;
      }

      return syncToken(token);
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Failed to update FCM status'},
      );
      return false;
    }
  }

  Future<void> _handleTokenRefresh(String newToken) async {
    try {
      await AppLogger.logMessage('Handling FCM token refresh');
      await syncToken(newToken);
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Failed to handle token refresh'},
      );
    }
  }

  Future<void> deregisterToken() async {
    await AppLogger.logMessage('Starting FCM token deregistration');
    final prefs = await SharedPreferences.getInstance();
    try {
      final deviceInfo = await DeviceInfo.getDeviceInfo();

      await AppLogger.logMessage(
        'Sending deregistration request',
        extras: {'device_id': deviceInfo.id},
      );

      final response = await _client.delete(
        WpUrls.fcmTokenPath,
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
}
