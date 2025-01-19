import 'package:bmb_mobile/features/http/wp_urls.dart';
import 'package:bmb_mobile/features/http/domain/service/app_password_http_client.dart';
import 'package:bmb_mobile/features/http/domain/service/session_http_client.dart';
import 'package:bmb_mobile/core/utils/app_logger.dart';
import 'dart:convert';
import 'package:bmb_mobile/features/auth/data/models/wp_app_password.dart';
import 'package:bmb_mobile/features/auth/data/models/wp_app_password_result.dart';
import 'package:bmb_mobile/features/auth/data/repositories/wp_credential_repository.dart';

class WpBasicAuth {
  final AppPasswordHttpClient _passwordClient;
  final SessionHttpClient _sessionClient;
  final WpCredentialRepository _credentialManager;
  static const String _appName = 'bmb-mobile-app';
  static const String _appId = '72b89be5-8c8d-480a-8a9f-d08324d8410a';

  WpBasicAuth(
      this._passwordClient, this._sessionClient, this._credentialManager);

  Future<bool> login(String username) async {
    try {
      await AppLogger.logMessage(
          'Attempting WP Basic Auth login for: $username');

      if (await _hasStoredPassword()) {
        await AppLogger.logMessage(
            'Using existing stored application password');
        return true;
      }

      await AppLogger.logMessage('Attempting to create application password');

      final result = await _createApplicationPassword(username);

      if (result.success && result.password != null) {
        await _credentialManager.storeCredentials(result.password!);
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
    final existingCredentials = await _credentialManager.getStoredCredentials();
    if (existingCredentials != null) {
      await AppLogger.logMessage('Found existing application password');
      return true;
    }
    return false;
  }

  Future<WpAppPasswordResult> _createApplicationPassword(
      String username) async {
    try {
      final initialResult = await _attemptCreatePassword(username);
      if (initialResult.success || initialResult.statusCode != 409) {
        return initialResult;
      }

      await AppLogger.logMessage(
        'Got conflict creating password, attempting to resolve',
      );

      return await _handlePasswordConflict(username);
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Error in create password flow'},
      );
      return WpAppPasswordResult.error();
    }
  }

  Future<WpAppPasswordResult> _handlePasswordConflict(String username) async {
    try {
      final existingUuid = await _findExistingPassword();
      if (existingUuid == null) {
        await AppLogger.logWarning('No existing password found after conflict');
        return WpAppPasswordResult.failure(409);
      }

      await AppLogger.logMessage(
        'Found existing password, deleting before retry',
        extras: {'uuid': existingUuid},
      );

      await _deletePassword(existingUuid);
      return await _attemptCreatePassword(username);
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Error handling password conflict'},
      );
      return WpAppPasswordResult.error();
    }
  }

  Future<WpAppPasswordResult> _attemptCreatePassword(String username) async {
    try {
      final response = await _sessionClient.post(
        WpUrls.applicationPasswordsUrl,
        body: {
          'name': _appName,
          'app_id': _appId,
        },
      );

      await AppLogger.logMessage(
        'Attempted to create password',
        extras: {'status_code': response?.statusCode},
      );

      if (response?.statusCode == 201) {
        final responseData = jsonDecode(response!.body);
        final appPassword = WpAppPassword(
          username: username,
          password: responseData['password'] as String,
          uuid: responseData['uuid'] as String,
        );
        await AppLogger.logMessage('Application password created successfully');
        return WpAppPasswordResult.success(appPassword, response.statusCode);
      }

      if (response?.statusCode != 409) {
        await AppLogger.logWarning(
          'Unexpected status code creating password',
          extras: {
            'status_code': response?.statusCode,
            'response': response?.body,
          },
        );
      }

      return WpAppPasswordResult.failure(response?.statusCode ?? 500);
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Error attempting to create password'},
      );
      return WpAppPasswordResult.error();
    }
  }

  Future<String?> _findExistingPassword() async {
    try {
      final response = await _sessionClient.get(
        WpUrls.applicationPasswordsUrl,
      );

      await AppLogger.logMessage(
        'Fetched existing passwords',
        extras: {'status_code': response?.statusCode},
      );

      if (response?.statusCode == 200) {
        final passwords = jsonDecode(response!.body) as List;
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
          'status_code': response?.statusCode,
          'response': response?.body,
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
    String uuid, {
    bool isLogout = false,
  }) async {
    final client = isLogout ? _passwordClient : _sessionClient;
    final response = await client.delete(
      WpUrls.applicationPasswordUrl(uuid),
    );

    return response?.statusCode == 200;
  }

  Future<void> logout() async {
    try {
      final credentials = await _credentialManager.getStoredCredentials();
      if (credentials != null) {
        final deleted = await _deletePassword(
          credentials.uuid,
          isLogout: true,
        );

        if (!deleted) {
          await AppLogger.logWarning(
            'Failed to delete application password on server',
            extras: {'uuid': credentials.uuid},
          );
        }
      }
      await AppLogger.logMessage('Logged out of basic auth successfully');
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Failed to log out of basic auth'},
      );
    } finally {
      AppLogger.logMessage('Removing application password from storage');
      await _credentialManager.clearCredentials();
    }
  }

  Future<bool> isAuthenticated() async {
    try {
      final credentials = await _credentialManager.getStoredCredentials();
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
}
