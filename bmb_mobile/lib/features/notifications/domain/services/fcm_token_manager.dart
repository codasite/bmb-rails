import 'package:bmb_mobile/features/wp_http/wp_urls.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:package_info_plus/package_info_plus.dart';
import 'package:bmb_mobile/core/utils/app_logger.dart';
import 'package:bmb_mobile/features/wp_http/domain/service/wp_http_client.dart';
import 'package:bmb_mobile/core/utils/device_info.dart';

class FcmTokenManager {
  static const String _tokenKey = 'fcm_token';
  final FirebaseMessaging _messaging;
  final WpHttpClient _client;
  final SharedPreferences _prefs;

  FcmTokenManager(
    this._client,
    this._messaging,
    this._prefs,
  );

  Future<void> requestPermissions() async {
    await AppLogger.debugLog("Requesting FCM permissions");
    await _messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );
    await AppLogger.debugLog('FCM permissions requested successfully');
  }

  /// Initialize FCM and request permissions
  Future<void> initialize() async {
    await AppLogger.debugLog('Initializing FCM');
    try {
      await AppLogger.debugLog('Setting up FCM token refresh listener');
      _messaging.onTokenRefresh.listen(_handleTokenRefresh);

      FirebaseMessaging.onMessage.listen(handleMessageReceived);

      await AppLogger.debugLog('Setting up initial token');
      await getNewToken();

      await AppLogger.debugLog('Finished initializing FCM');
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Failed to initialize FCM'},
      );
    }
  }

  Future<void> handleMessageReceived(RemoteMessage message) async {
    await AppLogger.debugLog('Handling FCM message: $message');
    await AppLogger.debugLog('Message data: ${message.data}');
    await AppLogger.debugLog('Message notification: ${message.notification}');
    await AppLogger.debugLog(
        'Message notification body: ${message.notification?.body}');
    await AppLogger.debugLog(
        'Message notification title: ${message.notification?.title}');
  }

  Future<void> getNewToken() async {
    final token = await _messaging.getToken();
    await AppLogger.debugLog('Got FCM token: $token');
    if (token == null) {
      await AppLogger.logWarning('Failed to get FCM token');
      return;
    }
    await syncToken(token);
  }

  Future<bool> syncToken(String token) async {
    try {
      await AppLogger.debugLog('Syncing FCM token');
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
        await AppLogger.debugLog('FCM token synced successfully');
        await _prefs.setString(_tokenKey, token);
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
      final token = _prefs.getString(_tokenKey);

      if (token == null) {
        await AppLogger.debugLog(
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
      await AppLogger.debugLog('Handling FCM token refresh');
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
    await AppLogger.debugLog('Starting FCM token deregistration');
    try {
      final deviceInfo = await DeviceInfo.getDeviceInfo();

      await AppLogger.debugLog(
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
        await AppLogger.debugLog('Successfully deregistered FCM token');
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
      await AppLogger.debugLog('Removing FCM token from SharedPreferences');
      await _prefs.remove(_tokenKey);
    }
  }
}
