import 'package:flutter/foundation.dart';
import 'package:sentry_flutter/sentry_flutter.dart';

/// Handles application logging with different levels of detail and optional Sentry integration.
/// Must be initialized before use with [initialize].
class AppLogger {
  const AppLogger._(); // Private constructor to prevent instantiation

  static bool _initialized = false;
  static bool _useSentry = false;
  static bool _debugUseSentry = false;

  static Future<void> initialize({
    required String dsn,
    required String environment,
    bool useSentry = true,
    bool debugUseSentry = false,
  }) async {
    _useSentry = useSentry;
    _debugUseSentry = debugUseSentry;
    _initialized = true;

    if (dsn.isNotEmpty && environment.isNotEmpty) {
      await SentryFlutter.init(
        (options) {
          options.dsn = dsn;
          options.tracesSampleRate = environment == 'production' ? 0.1 : 1.0;
          options.debug = kDebugMode;
          options.environment = environment;
        },
      );
    } else {
      _useSentry = false;
      _debugUseSentry = false;
    }
  }

  static void _checkInitialized() {
    assert(_initialized,
        'AppLogger must be initialized before use. Call AppLogger.initialize() first.');
  }

  /// Log errors and exceptions to both local logs and Sentry.
  /// ```dart
  /// logError(exception, stackTrace);
  /// logError('Something went wrong', null, message: 'Custom message');
  /// ```
  static Future<void> logError(
    dynamic exception,
    StackTrace? stackTrace, {
    Map<String, dynamic> extras = const {},
    String? message,
  }) async {
    final errorMessage = message ?? exception.toString();
    await _log(
      errorMessage,
      SentryLevel.error,
      extras: extras,
      exception: exception,
      stackTrace: stackTrace,
    );
  }

  static Future<void> logWarning(
    String message, {
    Map<String, dynamic> extras = const {},
  }) async {
    await _log(
      message,
      SentryLevel.warning,
      extras: extras,
    );
  }

  /// Use this for important application events (user actions, business operations, state changes).
  /// Sent to Sentry by default.
  static Future<void> logInfo(
    String message, {
    Map<String, dynamic> extras = const {},
  }) async {
    await _log(
      message,
      SentryLevel.info,
      extras: extras,
    );
  }

  /// Use this for development and debugging details (function calls, variables, API details).
  /// Only logged locally by default.
  static Future<void> debugLog(
    String message, {
    Map<String, dynamic> extras = const {},
    bool printStackTrace = false,
    bool printExtras = false,
  }) async {
    await _log(
      message,
      SentryLevel.debug,
      extras: extras,
      printStackTrace: printStackTrace,
      printExtras: printExtras,
    );
  }

  static Future<void> _log(
    String message,
    SentryLevel level, {
    Map<String, dynamic> extras = const {},
    dynamic exception,
    StackTrace? stackTrace,
    bool printStackTrace = false,
    bool printExtras = false,
  }) async {
    _checkInitialized();

    // Local logging
    if (kDebugMode) {
      final timestamp = DateTime.now().toLocal().toString().split('.').first;
      final prefix = level.name.toUpperCase();

      if (level == SentryLevel.debug) {
        // More detailed output for debug logs
        print('[$timestamp][$prefix] $message');
        if (extras.isNotEmpty && printExtras) {
          print('Context:');
          extras.forEach((key, value) => print('  $key: $value'));
        }
        // Include stack trace for debug logs to help with tracing
        if (printStackTrace) {
          print('Stack trace:');
          StackTrace.current
              .toString()
              .split('\n')
              .take(3)
              .forEach((line) => print('  $line'));
        }
      } else {
        // Simpler output for other log levels
        print('[$timestamp][$prefix] $message');
        if (extras.isNotEmpty) {
          print('Extras: $extras');
        }
      }
    }

    // Sentry reporting
    if (level == SentryLevel.debug ? _debugUseSentry : _useSentry) {
      if (exception != null) {
        await Sentry.captureException(
          exception,
          stackTrace: stackTrace,
          hint: Hint.withMap(extras),
        );
      } else {
        await Sentry.captureMessage(
          message,
          level: level,
          hint: Hint.withMap(extras),
        );
      }
    }
  }
}
