class AppConstants {
  static const String baseUrl = 'https://backmybracket.com';
  static const String loginPath = '/wp-login.php';
  static const String registerPath = '/wp-login.php?action=register';
  static const String lostPasswordPath = '/wp-login.php?action=lostpassword';

  static String get loginUrl => baseUrl + loginPath;
}
