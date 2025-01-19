import 'package:flutter/foundation.dart';
import 'package:bmb_mobile/auth/wp_auth.dart';
import 'package:bmb_mobile/auth/wp_basic_auth.dart';
import 'package:bmb_mobile/auth/wp_cookie_auth.dart';
import 'package:webview_cookie_manager/webview_cookie_manager.dart';
import 'package:bmb_mobile/providers/http_client_provider.dart';

class AuthProvider with ChangeNotifier {
  final HttpClientProvider _httpProvider;
  late final WpBasicAuth _basicAuth;
  late final WpCookieAuth _cookieAuth;
  late final WpAuth _auth;

  AuthProvider(this._httpProvider, WebviewCookieManager cookieManager) {
    _basicAuth = WpBasicAuth(
      _httpProvider.passwordClient,
      _httpProvider.sessionClient,
      _httpProvider.credentialManager,
    );
    _cookieAuth = WpCookieAuth(cookieManager);
    _auth = WpAuth(_cookieAuth, _basicAuth);
  }

  WpAuth get auth => _auth;

  Future<bool> login(String username, String password) async {
    final success = await _auth.login(username, password);
    notifyListeners();
    return success;
  }

  Future<void> logout() async {
    await _auth.logout();
    notifyListeners();
  }
}
