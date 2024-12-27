class AppConstants {
  static const String baseUrl = 'https://backmybracket.com';
  static const String loginPath = '/wp-login.php';

  static String get loginUrl => baseUrl + loginPath;
}
