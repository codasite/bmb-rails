import 'package:bmb_mobile/login/wp_cookie_auth.dart';
import 'package:bmb_mobile/login/wp_basic_auth.dart';

class WpAuth {
  final _cookieAuth = WpCookieAuth();
  final _basicAuth = WpBasicAuth();

  Future<bool> isAuthenticated() async {
    return await _cookieAuth.isAuthenticated() &&
        await _basicAuth.isAuthenticated();
  }

  Future<bool> login(String username, String password) async {
    final cookieLoggedIn = await _cookieAuth.login(username, password);
    if (!cookieLoggedIn) {
      return false;
    }
    final basicLoggedIn = await _basicAuth.login(username);
    return basicLoggedIn;
  }

  Future<void> logout() async {}
}
