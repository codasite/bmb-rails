class AppConstants {
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

  // Full URLs
  static String get loginUrl => baseUrl + loginPath;
  static String get applicationPasswordsUrl =>
      baseUrl + applicationPasswordsPath;
  static String applicationPasswordUrl(String uuid) =>
      '$applicationPasswordsUrl/$uuid';
}
