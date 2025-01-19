import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:bmb_mobile/core/utils/app_logger.dart';
import 'package:bmb_mobile/features/http/wp_urls.dart';

abstract class AuthenticatedHttpClient {
  const AuthenticatedHttpClient();

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

      final uri = Uri.parse('${WpUrls.baseUrl}$path');
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
