import 'package:bmb_mobile/core/utils/environment_config.dart';

class WpUrls {
  // static const String baseUrl = EnvironmentConfig.wpBaseUrl;
  static const String baseUrl = "http://192.168.1.77:8008";

  // WordPress API paths
  static const String _wpApiBase = '/wp-json/wp/v2';
  static const String _usersMe = '$_wpApiBase/users/me';
  static const String applicationPasswordsPath =
      '$_usersMe/application-passwords';

  // WordPress login paths
  static const String loginPath = '/wp-login.php';
  static const String registerPath = '/wp-login.php?action=register';
  static const String lostPasswordPath = '/wp-login.php?action=lostpassword';

  // FCM API paths
  static const String _fcmBase = '/wp-json/bmb/v1/fcm';
  static const String _tokenBase = '$_fcmBase/token';
  static const String fcmSyncPath = '$_tokenBase/sync';
  static const String fcmTokenPath = _tokenBase;

  // Notification API paths
  static const String _notificationBase = '/wp-json/bmb/v1/notifications';
  static String notificationPath(String id) => '$_notificationBase/$id';
  static String get notificationsPath => _notificationBase;
  static String notificationReadPath(String id) =>
      '${notificationPath(id)}/read';
  static String get notificationsReadAllPath => '$_notificationBase/read-all';

  // Full URLs
  static String get loginUrl => baseUrl + loginPath;
  static String applicationPasswordPath(String uuid) =>
      '$applicationPasswordsPath/$uuid';
}
