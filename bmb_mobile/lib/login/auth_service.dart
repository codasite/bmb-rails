import 'package:bmb_mobile/constants.dart';
import 'package:http/http.dart' as http;
import 'package:webview_cookie_manager/webview_cookie_manager.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:io';
import 'dart:convert';

class AuthService {
  final _cookieManager = WebviewCookieManager();
  static const String _cookieStorageKey = 'wordpress_cookies';

  Future<bool> login(String username, String password) async {
    try {
      final response = await http.post(
        Uri.parse(AppConstants.loginUrl),
        body: {
          'log': username,
          'pwd': password,
          'rememberme': 'forever',
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
            final cookie =
                Cookie(_getCookieName(cookieStr), _getCookieValue(cookieStr))
                  ..domain = uri.host
                  ..path = '/';
            cookiesToSet.add(cookie);
          }
        }

        // Set cookies in WebView
        await _cookieManager.setCookies(cookiesToSet);

        // Store cookies in SharedPreferences
        await _storeCookies(cookiesToSet);

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
      var cookies = await _cookieManager.getCookies(uri.toString());

      // If no cookies in WebView, try to restore from SharedPreferences
      if (!cookies
          .any((cookie) => cookie.name.contains('wordpress_logged_in_'))) {
        final restored = await _restoreCookies();
        if (restored) {
          cookies = await _cookieManager.getCookies(uri.toString());
        }
      }

      return cookies
          .any((cookie) => cookie.name.contains('wordpress_logged_in_'));
    } catch (e) {
      return false;
    }
  }

  Future<void> logout() async {
    await _cookieManager.clearCookies();
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_cookieStorageKey);
  }

  Future<void> _storeCookies(List<Cookie> cookies) async {
    final prefs = await SharedPreferences.getInstance();
    final cookieData = cookies
        .map((cookie) => {
              'name': cookie.name,
              'value': cookie.value,
              'domain': cookie.domain,
              'path': cookie.path,
            })
        .toList();

    await prefs.setString(_cookieStorageKey, jsonEncode(cookieData));
  }

  Future<bool> _restoreCookies() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final cookieString = prefs.getString(_cookieStorageKey);

      if (cookieString == null) return false;

      final cookieData = jsonDecode(cookieString) as List;
      final cookies = cookieData
          .map((data) => Cookie(data['name'], data['value'])
            ..domain = data['domain']
            ..path = data['path'])
          .toList();

      await _cookieManager.setCookies(cookies);
      return true;
    } catch (e) {
      return false;
    }
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
