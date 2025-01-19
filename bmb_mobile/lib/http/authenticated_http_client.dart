import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:bmb_mobile/utils/app_logger.dart';
import 'package:bmb_mobile/constants.dart';
import 'package:bmb_mobile/auth/wp_credential_manager.dart';
import 'package:webview_cookie_manager/webview_cookie_manager.dart';

abstract class AuthenticatedHttpClient {
  const AuthenticatedHttpClient();

  // Named constructors
  static AppPasswordHttpClient withCredentials(
      WpCredentialManager credentialManager) {
    return AppPasswordHttpClient(credentialManager);
  }

  static SessionHttpClient withSession(WebviewCookieManager cookieManager) {
    return SessionHttpClient(cookieManager);
  }

  // Abstract method that both implementations will provide
  Future<Map<String, String>?> getAuthHeaders();

  Future<http.Response?> get(
    String path, {
    Map<String, String>? headers,
  }) async {
    return _makeRequest('GET', path, headers: headers);
  }

  Future<http.Response?> post(
    String path, {
    Map<String, String>? headers,
    Object? body,
  }) async {
    return _makeRequest('POST', path, headers: headers, body: body);
  }

  Future<http.Response?> put(
    String path, {
    Map<String, String>? headers,
    Object? body,
  }) async {
    return _makeRequest('PUT', path, headers: headers, body: body);
  }

  Future<http.Response?> delete(
    String path, {
    Map<String, String>? headers,
    Object? body,
  }) async {
    return _makeRequest('DELETE', path, headers: headers, body: body);
  }

  Future<http.Response?> _makeRequest(
    String method,
    String path, {
    Map<String, String>? headers,
    Object? body,
  }) async {
    try {
      final authHeaders = await getAuthHeaders();
      if (authHeaders == null) {
        await AppLogger.logWarning(
            'No auth headers found for authenticated request');
        return null;
      }

      final uri = Uri.parse('${AppConstants.baseUrl}$path');
      final requestHeaders = {
        'Content-Type': 'application/json',
        ...authHeaders,
        ...?headers,
      };

      final encodedBody = body != null ? jsonEncode(body) : null;

      http.Response response;
      switch (method) {
        case 'GET':
          response = await http.get(uri, headers: requestHeaders);
          break;
        case 'POST':
          response =
              await http.post(uri, headers: requestHeaders, body: encodedBody);
          break;
        case 'PUT':
          response =
              await http.put(uri, headers: requestHeaders, body: encodedBody);
          break;
        case 'DELETE':
          response = await http.delete(uri,
              headers: requestHeaders, body: encodedBody);
          break;
        default:
          throw Exception('Unsupported HTTP method: $method');
      }

      await AppLogger.logMessage(
        'Made authenticated request',
        extras: {
          'path': path,
          'method': method,
          'status_code': response.statusCode,
        },
      );

      return response;
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {
          'message': 'Failed to make authenticated request',
          'path': path,
          'method': method,
        },
      );
      return null;
    }
  }
}

// Implementation using app password credentials
class AppPasswordHttpClient extends AuthenticatedHttpClient {
  final WpCredentialManager credentialManager;

  const AppPasswordHttpClient(this.credentialManager);

  @override
  Future<Map<String, String>?> getAuthHeaders() async {
    final credentials = await credentialManager.getStoredCredentials();
    if (credentials == null) return null;

    return {
      'Authorization': credentials.basicAuth,
    };
  }
}

// Implementation using session cookies and nonce
class SessionHttpClient extends AuthenticatedHttpClient {
  final WebviewCookieManager cookieManager;

  const SessionHttpClient(this.cookieManager);

  @override
  Future<Map<String, String>?> getAuthHeaders() async {
    try {
      final baseUri = Uri.parse(AppConstants.baseUrl);
      final cookies = await cookieManager.getCookies(baseUri.toString());

      final restNonceCookie = cookies.firstWhere(
        (cookie) => cookie.name == 'wordpress_rest_nonce',
        orElse: () {
          throw Exception('WordPress REST nonce cookie not found');
        },
      );

      final nonce = restNonceCookie.value;
      final cookieHeader =
          cookies.map((c) => '${c.name}=${c.value}').join('; ');

      return {
        'X-WP-Nonce': nonce,
        'Cookie': cookieHeader,
      };
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Failed to generate auth headers'},
      );
      return null;
    }
  }
}
