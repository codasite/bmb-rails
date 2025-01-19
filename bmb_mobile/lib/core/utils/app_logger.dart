import 'package:flutter/foundation.dart';
import 'package:sentry_flutter/sentry_flutter.dart';

class AppLogger {
  static Future<void> initialize({
    required String dsn,
    required String environment,
  }) async {
    await SentryFlutter.init(
      (options) {
        options.dsn = dsn;
        // Set traces_sample_rate to 1.0 to capture 100% of transactions for performance monitoring.
        // We recommend adjusting this value in production.
        options.tracesSampleRate = environment == 'production' ? 0.1 : 1.0;
        options.debug = kDebugMode;
        options.environment = environment;
      },
    );
  }

  static Future<void> logError(
    dynamic exception,
    StackTrace? stackTrace, {
    Map<String, dynamic> extras = const {},
  }) async {
    if (kDebugMode) {
      print('ERROR: $extras');
      print(exception);
      print(stackTrace);
    }

    await Sentry.captureException(
      exception,
      stackTrace: stackTrace,
      hint: Hint.withMap(extras),
    );
  }

  static Future<void> logMessage(
    String message, {
    SentryLevel level = SentryLevel.info,
    Map<String, dynamic> extras = const {},
  }) async {
    if (kDebugMode) {
      print('${level.name.toUpperCase()}: $message');
      if (extras.isNotEmpty) {
        print(extras);
      }
    }

    await Sentry.captureMessage(
      message,
      level: level,
      hint: Hint.withMap(extras),
    );
  }

  static Future<void> logWarning(
    String message, {
    Map<String, dynamic> extras = const {},
  }) async {
    await logMessage(message, level: SentryLevel.warning, extras: extras);
  }
}
