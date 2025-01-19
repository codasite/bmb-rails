import 'package:bmb_mobile/constants.dart';
import 'package:bmb_mobile/utils/app_logger.dart';
import 'package:http/http.dart' as http;
import 'package:webview_cookie_manager/webview_cookie_manager.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:io';
import 'dart:convert';

// 1. user login
// - POST /wp-login.php
// - set logged in cookie
// - check if app password exists
// - if not, GET /application-passwords, find one named 'bmb-mobile-app'
// - if not found, POST to /application-passwords
// - store the app password in shared prefs
// 2. user logout
// - remove logged in cookie
// - DELETE /application-passwords
// - remove app password

class WpCookieAuth {
  final WebviewCookieManager _cookieManager;
  static const String _cookieStorageKey = 'wordpress_cookies';

  const WpCookieAuth(this._cookieManager);

  Future<bool> login(String username, String password) async {
    try {
      await AppLogger.logMessage('Attempting login for user: $username');

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
        // Handle cookie storage as before
        final cookiesList = cookies.split(',').map((cookie) => cookie.trim());
        final uri = Uri.parse(AppConstants.baseUrl);

        final cookiesToSet = <Cookie>[];
        for (var cookieStr in cookiesList) {
          if (cookieStr.contains('wordpress')) {
            final cookie =
                Cookie(_getCookieName(cookieStr), _getCookieValue(cookieStr))
                  ..domain = uri.host
                  ..path = '/';
            cookiesToSet.add(cookie);
          }
        }

        await _cookieManager.setCookies(cookiesToSet);
        await _storeCookies(cookiesToSet);
        await AppLogger.logMessage('Cookies set successfully');
        return true;
      }

      await AppLogger.logWarning(
        'Login failed - no valid WordPress cookie received',
      );
      return false;
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Login attempt failed'},
      );
      return false;
    }
  }

  Future<bool> isAuthenticated() async {
    try {
      final uri = Uri.parse(AppConstants.baseUrl);
      var cookies = await _cookieManager.getCookies(uri.toString());

      if (!cookies
          .any((cookie) => cookie.name.contains('wordpress_logged_in_'))) {
        final restored = await _restoreCookies();
        if (restored) {
          cookies = await _cookieManager.getCookies(uri.toString());
        }
      }

      final hasValidCookie =
          cookies.any((cookie) => cookie.name.contains('wordpress_logged_in_'));
      if (!hasValidCookie) {
        await AppLogger.logWarning(
          'No valid WordPress cookie found',
        );
      }
      return hasValidCookie;
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Cookie validation check failed'},
      );
      return false;
    }
  }

  Future<void> logout() async {
    try {
      await _cookieManager.clearCookies();
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(_cookieStorageKey);
      await AppLogger.logMessage('Logged out of cookie auth successfully');
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Failed to log out of cookie auth'},
      );
    }
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
