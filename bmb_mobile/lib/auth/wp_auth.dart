import 'package:bmb_mobile/auth/wp_cookie_auth.dart';
import 'package:bmb_mobile/auth/wp_basic_auth.dart';
import 'package:bmb_mobile/utils/app_logger.dart';

class WpAuth {
  final WpCookieAuth _cookieAuth;
  final WpBasicAuth _basicAuth;

  const WpAuth(this._cookieAuth, this._basicAuth);

  Future<bool> isAuthenticated() async {
    return await _cookieAuth.isAuthenticated();
  }

  Future<bool> login(String username, String password) async {
    final cookieLoggedIn = await _cookieAuth.login(username, password);
    if (!cookieLoggedIn) {
      return false;
    }
    final basicLoggedIn = await _basicAuth.login(username);
    if (!basicLoggedIn) {
      AppLogger.logError(
          'Failed to login with basic auth. Notifications will not work.',
          null);
    }
    return true;
  }

  Future<void> logout() async {
    await _cookieAuth.logout();
    await _basicAuth.logout();
  }
}
