class AppConstants {
  // static const String baseUrl = 'https://backmybracket.com';
  static const String baseUrl = 'http://localhost:8008';
  static const String loginPath = '/wp-login.php';
  static const String registerPath = '/wp-login.php?action=register';
  static const String lostPasswordPath = '/wp-login.php?action=lostpassword';
  static const String applicationPasswordsPath =
      '/wp-json/wp/v2/users/me/application-passwords';

  static String get loginUrl => baseUrl + loginPath;
  static String get applicationPasswordsUrl =>
      baseUrl + applicationPasswordsPath;
}
