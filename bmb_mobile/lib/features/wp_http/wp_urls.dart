class WpUrls {
  static const String baseUrl = 'http://localhost:8008';

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
  static const String fcmRegisterPath = '$_fcmBase/register';
  static const String fcmUpdatePath = '$_fcmBase/update';
  static const String fcmDeregisterPath = '$_fcmBase/deregister';
  static const String fcmStatusPath = '$_fcmBase/status';

  // Full URLs
  static String get loginUrl => baseUrl + loginPath;
  static String applicationPasswordPath(String uuid) =>
      '$applicationPasswordsPath/$uuid';
}
