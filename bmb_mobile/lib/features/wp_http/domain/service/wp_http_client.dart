import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:bmb_mobile/core/utils/app_logger.dart';
import 'package:bmb_mobile/features/wp_http/wp_urls.dart';
import 'package:bmb_mobile/features/wp_http/data/models/retry_config.dart';

abstract class WpHttpClient {
  const WpHttpClient();

  // Abstract method that both implementations will provide
  Future<Map<String, String>?> getAuthHeaders();

  Future<http.Response?> get(
    String path, {
    Map<String, String>? headers,
    RetryConfig? retryConfig,
  }) async {
    return _makeRequest(
      'GET',
      path,
      headers: headers,
      retryConfig: retryConfig,
    );
  }

  Future<http.Response?> post(
    String path, {
    Map<String, String>? headers,
    Object? body,
    RetryConfig? retryConfig,
  }) async {
    return _makeRequest(
      'POST',
      path,
      headers: headers,
      body: body,
      retryConfig: retryConfig,
    );
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
    RetryConfig? retryConfig,
  }) async {
    int attempts = 0;
    final config = retryConfig ?? const RetryConfig();
    final shouldRetry = config.shouldRetry ?? RetryConfig.defaultShouldRetry;
    final backoff = config.backoff ?? RetryConfig.defaultBackoff;

    while (attempts <= config.maxRetries) {
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
            response = await http.post(uri,
                headers: requestHeaders, body: encodedBody);
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

        await AppLogger.debugLog(
          'Made authenticated request',
          extras: {
            'path': path,
            'method': method,
            'status_code': response.statusCode,
            'response_body': response.body,
            'attempt': attempts + 1,
          },
        );

        if (!shouldRetry(response.statusCode)) {
          return response;
        }

        if (attempts == config.maxRetries) {
          await AppLogger.logWarning(
            'Request failed after max retries',
            extras: {
              'path': path,
              'method': method,
              'max_retries': config.maxRetries,
            },
          );
          return response;
        }

        final delay = backoff(attempts);
        await AppLogger.debugLog(
          'Retrying request after delay',
          extras: {
            'attempt': attempts + 1,
            'delay_seconds': delay.inSeconds,
          },
        );
        await Future.delayed(delay);
        attempts++;
      } catch (e, stackTrace) {
        if (!shouldRetry(null) || attempts == config.maxRetries) {
          await AppLogger.logError(
            e,
            stackTrace,
            extras: {
              'message': 'Request failed',
              'path': path,
              'method': method,
              'attempt': attempts + 1,
            },
          );
          return null;
        }
        attempts++;
      }
    }
    return null;
  }
}
