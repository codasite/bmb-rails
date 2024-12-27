import 'package:bmb_mobile/constants.dart';
import 'package:http/http.dart' as http;
import 'package:webview_cookie_manager/webview_cookie_manager.dart';
import 'dart:io';

class AuthService {
  final _cookieManager = WebviewCookieManager();

  Future<bool> login(String username, String password) async {
    try {
      final response = await http.post(
        Uri.parse(AppConstants.loginUrl),
        body: {
          'log': username,
          'pwd': password,
        },
      );

      // Check for WordPress login cookie in response headers
      final cookies = response.headers['set-cookie'];
      if (cookies != null && cookies.contains('wordpress_logged_in_')) {
        // Parse the Set-Cookie header and set it in the WebView cookie manager
        final cookiesList = cookies.split(',').map((cookie) => cookie.trim());
        final uri = Uri.parse(AppConstants.baseUrl);

        final cookiesToSet = <Cookie>[];
        for (var cookieStr in cookiesList) {
          // Only process WordPress cookies
          if (cookieStr.contains('wordpress')) {
            cookiesToSet.add(
                Cookie(_getCookieName(cookieStr), _getCookieValue(cookieStr))
                  ..domain = uri.host
                  ..path = '/');
          }
        }

        await _cookieManager.setCookies(cookiesToSet);
        return true;
      }
      return false;
    } catch (e) {
      return false;
    }
  }

  Future<bool> hasValidCookie() async {
    try {
      final uri = Uri.parse(AppConstants.baseUrl);
      final cookies = await _cookieManager.getCookies(uri.toString());
      return cookies
          .any((cookie) => cookie.name.contains('wordpress_logged_in_'));
    } catch (e) {
      return false;
    }
  }

  Future<void> logout() async {
    await _cookieManager.clearCookies();
  }

  String _getCookieName(String cookieStr) {
    final nameValue = cookieStr.split(';')[0];
    return nameValue.split('=')[0];
  }

  String _getCookieValue(String cookieStr) {
    final nameValue = cookieStr.split(';')[0];
    return nameValue.split('=')[1];
  }
}
