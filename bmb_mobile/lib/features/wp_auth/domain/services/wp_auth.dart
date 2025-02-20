import 'package:bmb_mobile/features/wp_auth/domain/services/wp_cookie_auth.dart';
import 'package:bmb_mobile/features/wp_auth/domain/services/wp_basic_auth.dart';
import 'package:bmb_mobile/core/utils/app_logger.dart';
import 'package:bmb_mobile/features/wp_http/wp_urls.dart';
import 'package:http/http.dart' as http;
import 'package:html/parser.dart';

class WpAuth {
  final WpCookieAuth _cookieAuth;
  final WpBasicAuth _basicAuth;
  bool _isAuthenticated = false;
  final List<String> _errorsList = [];
  WpAuth(this._cookieAuth, this._basicAuth);

  bool get isAuthenticated => _isAuthenticated;
  List<String> getErrorList() {
    return _errorsList;
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
        await AppLogger.debugLog('Received 200 response. Parsing HTML');
        final document = parse(response.body);
        final errors = document.querySelector('#login_error');
        if (errors != null) {
          final errorList = errors.querySelectorAll('ul li');
          final singleError = errors.querySelector('p');
          if (errorList.isNotEmpty) {
            AppLogger.debugLog('Error count: ${errorList.length}');
            for (var error in errorList) {
              final errorMessage = error.text;
              _errorsList.add(errorMessage.replaceFirst('Error: ', ''));
              await AppLogger.debugLog(errorMessage);
            }
          } else if (singleError != null) {
            final errorMessage = singleError.text;
            _errorsList.add(errorMessage.replaceFirst('Error: ', ''));
            await AppLogger.debugLog('Single error: $errorMessage');
          }
        }
        AppLogger.debugLog('Errors: ${_errorsList}');
      }
      return false;
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Registration attempt failed'},
      );
      return false;
    }
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
