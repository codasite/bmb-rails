import 'package:bmb_mobile/features/wp_auth/domain/services/wp_cookie_auth.dart';
import 'package:bmb_mobile/features/wp_auth/domain/services/wp_basic_auth.dart';
import 'package:bmb_mobile/core/utils/app_logger.dart';
import 'package:bmb_mobile/features/wp_http/wp_urls.dart';
import 'package:http/http.dart' as http;
import 'package:http/http.dart';
import 'package:html/parser.dart';

class WpAuth {
  final WpCookieAuth _cookieAuth;
  final WpBasicAuth _basicAuth;
  bool _isAuthenticated = false;
  List<String> _errorsList = [];
  String? _resetPasswordCookie;

  WpAuth(this._cookieAuth, this._basicAuth);

  bool get isAuthenticated => _isAuthenticated;
  List<String> getErrorList() {
    return _errorsList;
  }

  List<String> _parseErrorsFromHtml(String htmlBody) {
    final document = parse(htmlBody);
    List<String> errors = [];
    final errorsEl = document.querySelector('#login_error');
    if (errorsEl != null) {
      final errorList = errorsEl.querySelectorAll('ul li');
      final singleError = errorsEl.querySelector('p');
      if (errorList.isNotEmpty) {
        AppLogger.debugLog('Error count: ${errorList.length}');
        for (var error in errorList) {
          final errorMessage = error.text;
          errors.add(errorMessage.replaceFirst('Error: ', ''));
          AppLogger.debugLog(errorMessage);
        }
      } else if (singleError != null) {
        final errorMessage = singleError.text;
        errors.add(errorMessage.replaceFirst('Error: ', ''));
        AppLogger.debugLog('Single error: $errorMessage');
      }
    }
    AppLogger.debugLog('Errors: $errors');
    return errors;
  }

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

  Future<bool> register(String email, String username) async {
    _errorsList.clear();
    try {
      await AppLogger.debugLog('Attempting registration for user: $email');

      final response = await http.post(
        Uri.parse(WpUrls.registerUrl),
        body: {
          'user_login': username,
          'user_email': email,
          'wp-submit': 'Create+Account',
        },
      );
      if (response.statusCode == 302) {
        await AppLogger.debugLog(
          'Received 302 redirect. Assuming registration successful',
        );
        return true;
      } else if (response.statusCode == 200) {
        _errorsList = _parseErrorsFromHtml(response.body);
      }
      return false;
    } catch (e, stackTrace) {
      _errorsList.add('Registration attempt failed');
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Registration attempt failed'},
      );
      return false;
    }
  }

  Future<bool> requestPasswordReset(String email) async {
    _errorsList.clear();
    try {
      await AppLogger.debugLog('Attempting password reset for user: $email');

      final response = await http.post(
        Uri.parse(WpUrls.lostPasswordUrl),
        body: {
          'user_login': email,
          'wp-submit': 'Get+New+Password',
        },
      );
      if (response.statusCode == 302) {
        await AppLogger.debugLog(
          'Received 302 redirect. Assuming password reset successful',
        );
        return true;
      } else if (response.statusCode == 200) {
        _errorsList = _parseErrorsFromHtml(response.body);
      }
      return false;
    } catch (e, stackTrace) {
      _errorsList.add('Password reset attempt failed');
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Password reset attempt failed'},
      );
      return false;
    }
  }

  Future<bool> validateResetPasswordLink(Uri resetLink) async {
    _errorsList.clear();
    final client = Client();
    try {
      AppLogger.debugLog('Validating reset password link: $resetLink');

      if (!resetLink.toString().contains(WpUrls.rpPath)) {
        _errorsList.add('Invalid password reset link');
        return false;
      }

      final key = resetLink.queryParameters['key'];
      final login = resetLink.queryParameters['login'];
      if (key == null || login == null) {
        _errorsList.add('Missing required parameters in password reset link');
        return false;
      }

      // First request to get the cookie
      final initialRequest = Request('GET', resetLink)..followRedirects = false;
      final initialResponse = await client.send(initialRequest);
      final cookies = initialResponse.headers['set-cookie'];
      AppLogger.debugLog('Initial response cookies: $cookies');

      if (initialResponse.statusCode == 302 && cookies != null) {
        _resetPasswordCookie = _extractCookie(initialResponse.headers);
        AppLogger.debugLog('Extracted cookie: $_resetPasswordCookie');

        // Follow up request with the cookie
        final request = Request('GET', resetLink);
        if (_resetPasswordCookie != null) {
          request.headers['Cookie'] = _resetPasswordCookie!;
        }

        final streamedResponse = await client.send(request);
        final response = await Response.fromStream(streamedResponse);
        AppLogger.debugLog('Response: ${response.body}');

        if (response.statusCode == 200) {
          AppLogger.debugLog('Received 200 status code. Parsing response');
          final document = parse(response.body);
          final form = document.querySelector('#resetpassform');
          if (form == null) {
            _errorsList = _parseErrorsFromHtml(response.body);
            if (_errorsList.isEmpty) {
              _errorsList.add('Invalid or expired reset password link');
            }
            AppLogger.debugLog(
                'Reset password form not found. Link may be invalid.');
            return false;
          }
          AppLogger.debugLog('Reset password link is valid');
          return true;
        }
      }

      AppLogger.debugLog(
          'Unexpected status code: ${initialResponse.statusCode}');
      _errorsList.add('Invalid or expired reset password link');
      return false;
    } catch (e, stackTrace) {
      _errorsList.add('Failed to validate reset password link');
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Reset password link validation failed'},
      );
      return false;
    } finally {
      client.close();
    }
  }

  String? _extractCookie(Map<String, String> headers) {
    final cookies = headers['set-cookie'];
    if (cookies == null) return null;

    // Extract wp-resetpass cookie
    final cookiesList = cookies.split(',');
    for (var cookie in cookiesList) {
      if (cookie.contains('wp-resetpass-')) {
        return cookie.split(';')[0];
      }
    }
    return null;
  }

  Future<bool> resetPassword(String key, String password) async {
    AppLogger.debugLog('Resetting password with key: $key');
    _errorsList.clear();
    try {
      final response = await http.post(
        Uri.parse(WpUrls.resetPasswordUrl),
        headers: _resetPasswordCookie != null
            ? {'Cookie': _resetPasswordCookie!}
            : null,
        body: {
          'pass1': password,
          'pass2': password,
          'rp_key': key,
          'wp-submit': 'Save+Password',
        },
      );
      AppLogger.debugLog(
          'Reset password response code: ${response.statusCode}');

      if (response.statusCode == 200) {
        AppLogger.debugLog('Password reset successful');
        return true;
      }

      AppLogger.debugLog('Unexpected status code: ${response.statusCode}');
      _errorsList = _parseErrorsFromHtml(response.body);
      return false;
    } catch (e, stackTrace) {
      _errorsList.add('Password reset attempt failed');
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Password reset attempt failed'},
      );
      return false;
    }
  }

  Future<void> logout() async {
    await _cookieAuth.logout();
    await _basicAuth.logout();
    _isAuthenticated = false;
  }
}
