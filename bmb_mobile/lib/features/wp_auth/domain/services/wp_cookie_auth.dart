import 'package:bmb_mobile/features/wp_http/wp_urls.dart';
import 'package:bmb_mobile/core/utils/app_logger.dart';
import 'package:http/http.dart' as http;
import 'package:webview_cookie_manager/webview_cookie_manager.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:io';
import 'dart:convert';
import 'dart:math';
import 'package:html/parser.dart';

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

  Future<bool> requestPasswordReset(String email) async {
    try {
      await AppLogger.debugLog('Attempting password reset for email: $email');

      // First get the lost password page to get the nonce
      final lostPasswordPage = await http.get(
        Uri.parse(WpUrls.baseUrl + WpUrls.lostPasswordPath),
        headers: {
          'User-Agent':
              'Mozilla/5.0 (iPhone; CPU iPhone OS 17_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.3.1 Mobile/15E148 Safari/604.1',
          'Accept':
              'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
          'Accept-Language': 'en-US,en;q=0.9',
        },
      );

      await AppLogger.debugLog(
        'Lost password page response',
        extras: {
          'status_code': lostPasswordPage.statusCode,
          'body_length': lostPasswordPage.body.length,
          'body_preview': lostPasswordPage.body
              .substring(0, min(200, lostPasswordPage.body.length)),
        },
      );

      // Extract nonce from the form
      final nonceMatch = RegExp(r'name="_wpnonce" value="([^"]+)"')
              .firstMatch(lostPasswordPage.body) ??
          RegExp(r'name="woocommerce-lost-password-nonce" value="([^"]+)"')
              .firstMatch(lostPasswordPage.body);

      final nonce = nonceMatch?.group(1);

      if (nonce == null) {
        await AppLogger.logWarning(
          'Failed to get password reset nonce',
          extras: {'response_body': lostPasswordPage.body},
        );
        return false;
      }

      // Submit password reset form
      final response = await http.post(
        Uri.parse(WpUrls.baseUrl + WpUrls.lostPasswordPath),
        body: {
          'user_login': email,
          'redirect_to': '',
          'wp-submit': 'Get New Password',
          '_wpnonce': nonce,
        },
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'User-Agent':
              'Mozilla/5.0 (iPhone; CPU iPhone OS 17_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.3.1 Mobile/15E148 Safari/604.1',
          'Accept':
              'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
          'Accept-Language': 'en-US,en;q=0.9',
          'Origin': WpUrls.baseUrl,
          'Referer': WpUrls.baseUrl + WpUrls.lostPasswordPath,
        },
      );

      // WordPress returns a 302 redirect on successful password reset request
      if (response.statusCode == 302) {
        await AppLogger.debugLog('Password reset email sent successfully');
        return true;
      }

      await AppLogger.logWarning(
        'Password reset request failed',
        extras: {
          'status_code': response.statusCode,
          'response': response.body,
        },
      );
      return false;
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Password reset attempt failed'},
      );
      return false;
    }
  }

  Future<bool> login(String username, String password) async {
    try {
      await AppLogger.debugLog('Attempting login for user: $username');

      final response = await http.post(
        Uri.parse(WpUrls.loginUrl),
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
        final uri = Uri.parse(WpUrls.baseUrl);

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
        await AppLogger.debugLog('Cookies set successfully');
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
      final uri = Uri.parse(WpUrls.baseUrl);
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
      await AppLogger.debugLog('Logged out of cookie auth successfully');
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
