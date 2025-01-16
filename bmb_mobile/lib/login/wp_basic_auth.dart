import 'package:bmb_mobile/constants.dart';
import 'package:bmb_mobile/utils/app_logger.dart';
import 'package:http/http.dart' as http;
import 'package:webview_cookie_manager/webview_cookie_manager.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';
import 'package:bmb_mobile/login/models/wp_app_password.dart';
import 'package:bmb_mobile/login/models/wp_app_password_result.dart';

class WpBasicAuth {
  final _cookieManager = WebviewCookieManager();
  static const String _appPasswordStorageKey = 'wp_app_password';
  static const String _appName = 'bmb-mobile-app';
  static const String _appId = 'com.backmybracket.mobile';

  Future<bool> login(String username) async {
    try {
      await AppLogger.logMessage(
          'Attempting WP Basic Auth login for: $username');

      if (await _hasStoredPassword()) {
        await AppLogger.logMessage(
            'Using existing stored application password');
        return true;
      }

      final authHeaders = await _getAuthHeaders();
      await AppLogger.logMessage(
          'Got auth headers, attempting to create application password');

      final result = await _createApplicationPassword(authHeaders);

      if (result.success && result.password != null) {
        await _storeApplicationPassword(result.password!);
        await AppLogger.logMessage(
            'Successfully created and stored application password');
        return true;
      }

      await AppLogger.logWarning(
        'Failed to obtain application password',
        extras: {
          'status_code': result.statusCode,
          'username': username,
        },
      );
      return false;
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {
          'message': 'Application password request failed',
          'username': username,
        },
      );
      return false;
    }
  }

  Future<bool> _hasStoredPassword() async {
    final existingCredentials = await getStoredCredentials();
    if (existingCredentials != null) {
      await AppLogger.logMessage('Found existing application password');
      return true;
    }
    return false;
  }

  Future<Map<String, String>> _getAuthHeaders() async {
    try {
      final baseUri = Uri.parse(AppConstants.baseUrl);
      final cookies = await _cookieManager.getCookies(baseUri.toString());

      final restNonceCookie = cookies.firstWhere(
        (cookie) => cookie.name == 'wordpress_rest_nonce',
        orElse: () {
          throw Exception('WordPress REST nonce cookie not found');
        },
      );

      final nonce = restNonceCookie.value;
      final cookieHeader =
          cookies.map((c) => '${c.name}=${c.value}').join('; ');

      await AppLogger.logMessage(
          'Generated auth headers with nonce and cookies');
      return {
        'Content-Type': 'application/json',
        'X-WP-Nonce': nonce,
        'Cookie': cookieHeader,
      };
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Failed to generate auth headers'},
      );
      rethrow;
    }
  }

  Future<WpAppPasswordResult> _createApplicationPassword(
    Map<String, String> headers,
  ) async {
    try {
      final initialResult = await _attemptCreatePassword(headers);
      if (initialResult.success || initialResult.statusCode != 409) {
        return initialResult;
      }

      await AppLogger.logMessage(
        'Got conflict creating password, attempting to resolve',
      );

      return await _handlePasswordConflict(headers);
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Error in create password flow'},
      );
      return WpAppPasswordResult.error();
    }
  }

  Future<WpAppPasswordResult> _handlePasswordConflict(
    Map<String, String> headers,
  ) async {
    try {
      final existingUuid = await _findExistingPassword(headers);
      if (existingUuid == null) {
        await AppLogger.logWarning('No existing password found after conflict');
        return WpAppPasswordResult.failure(409);
      }

      await AppLogger.logMessage(
        'Found existing password, deleting before retry',
        extras: {'uuid': existingUuid},
      );

      await _deletePassword(existingUuid, headers);
      return await _attemptCreatePassword(headers);
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Error handling password conflict'},
      );
      return WpAppPasswordResult.error();
    }
  }

  Future<WpAppPasswordResult> _attemptCreatePassword(
    Map<String, String> headers,
  ) async {
    try {
      final response = await http.post(
        Uri.parse(AppConstants.applicationPasswordsUrl),
        headers: headers,
        body: jsonEncode({
          'name': _appName,
          'app_id': _appId,
        }),
      );

      await AppLogger.logMessage(
        'Attempted to create password',
        extras: {'status_code': response.statusCode},
      );

      if (response.statusCode == 201) {
        final responseData = jsonDecode(response.body);
        final appPassword = WpAppPassword(
          username: responseData['user']['login'] as String,
          password: responseData['password'] as String,
          uuid: responseData['uuid'] as String,
        );
        await AppLogger.logMessage('Application password created successfully');
        return WpAppPasswordResult.success(appPassword, response.statusCode);
      }

      if (response.statusCode != 409) {
        await AppLogger.logWarning(
          'Unexpected status code creating password',
          extras: {
            'status_code': response.statusCode,
            'response': response.body,
          },
        );
      }

      return WpAppPasswordResult.failure(response.statusCode);
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Error attempting to create password'},
      );
      return WpAppPasswordResult.error();
    }
  }

  Future<String?> _findExistingPassword(
    Map<String, String> headers,
  ) async {
    try {
      final response = await http.get(
        Uri.parse(AppConstants.applicationPasswordsUrl),
        headers: headers,
      );

      await AppLogger.logMessage(
        'Fetched existing passwords',
        extras: {'status_code': response.statusCode},
      );

      if (response.statusCode == 200) {
        final passwords = jsonDecode(response.body) as List;
        final existing = passwords.firstWhere(
          (p) => p['name'] == _appName,
          orElse: () => null,
        );

        if (existing != null) {
          final uuid = existing['uuid'] as String;
          await AppLogger.logMessage(
            'Found existing password',
            extras: {'uuid': uuid},
          );
          return uuid;
        } else {
          await AppLogger.logMessage('No existing password found');
        }
      }

      await AppLogger.logWarning(
        'Failed to fetch existing passwords',
        extras: {
          'status_code': response.statusCode,
          'response': response.body,
        },
      );
      return null;
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Error finding existing password'},
      );
      return null;
    }
  }

  Future<bool> _deletePassword(
    String uuid,
    Map<String, String> headers,
  ) async {
    final response = await http.delete(
      Uri.parse(AppConstants.applicationPasswordUrl(uuid)),
      headers: headers,
    );

    return response.statusCode == 200;
  }

  Future<void> logout() async {
    try {
      final credentials = await getStoredCredentials();
      if (credentials != null) {
        final authHeaders = await _getAuthHeaders();
        final deleted = await _deletePassword(credentials.uuid, authHeaders);

        if (!deleted) {
          await AppLogger.logWarning(
            'Failed to delete application password on server',
            extras: {'uuid': credentials.uuid},
          );
        }
      }

      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(_appPasswordStorageKey);
      await AppLogger.logMessage('Logged out of basic auth successfully');
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Failed to log out of basic auth'},
      );
    }
  }

  Future<bool> isAuthenticated() async {
    try {
      final credentials = await getStoredCredentials();
      return credentials != null;
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Failed to check basic auth credentials'},
      );
      return false;
    }
  }

  Future<void> _storeApplicationPassword(WpAppPassword appPassword) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(
        _appPasswordStorageKey, jsonEncode(appPassword.toJson()));
  }

  Future<WpAppPassword?> getStoredCredentials() async {
    final prefs = await SharedPreferences.getInstance();
    final credentialsString = prefs.getString(_appPasswordStorageKey);
    if (credentialsString != null) {
      final json = jsonDecode(credentialsString) as Map<String, dynamic>;
      return WpAppPassword.fromJson(json);
    }
    return null;
  }
}
