import 'dart:math';

class RetryConfig {
  final int maxRetries;
  final bool Function(int? statusCode)? shouldRetry;
  final Duration Function(int attempt)? backoff;

  const RetryConfig({
    this.maxRetries = 3,
    this.shouldRetry,
    this.backoff,
  });

  static bool defaultShouldRetry(int? statusCode) {
    // Retry on network errors (null) or server errors (500+)
    return statusCode == null || statusCode >= 500;
  }

  static Duration defaultBackoff(int attempt) {
    // Exponential backoff with jitter
    return Duration(seconds: (1 << attempt) + (Random().nextInt(3)));
  }
}
