import 'package:bmb_mobile/features/wp_auth/domain/services/wp_cookie_auth.dart';
import 'package:bmb_mobile/features/wp_auth/domain/services/wp_basic_auth.dart';
import 'package:bmb_mobile/core/utils/app_logger.dart';

class WpAuth {
  final WpCookieAuth _cookieAuth;
  final WpBasicAuth _basicAuth;
  bool _isAuthenticated = false;

  WpAuth(this._cookieAuth, this._basicAuth);

  bool get isAuthenticated => _isAuthenticated;

  Future<void> refreshAuthStatus() async {
    _isAuthenticated = await _cookieAuth.isAuthenticated();
  }

  Future<bool> login(String username, String password) async {
    final cookieLoggedIn = await _cookieAuth.login(username, password);
    if (!cookieLoggedIn) {
      _isAuthenticated = false;
      return false;
    }

    final basicLoggedIn = await _basicAuth.login(username);
    if (!basicLoggedIn) {
      AppLogger.logError(
          'Failed to login with basic auth. Notifications will not work.',
          null);
    }

    _isAuthenticated = true;
    return true;
  }

  Future<bool> register(String email, String password) async {
    final success = await _cookieAuth.register(email, password);
    if (!success) {
      return false;
    }

    // Don't attempt to login after registration since password will be emailed
    return true;
  }

  Future<bool> requestPasswordReset(String email) async {
    return await _cookieAuth.requestPasswordReset(email);
  }

  Future<void> logout() async {
    await _cookieAuth.logout();
    await _basicAuth.logout();
    _isAuthenticated = false;
  }
}
