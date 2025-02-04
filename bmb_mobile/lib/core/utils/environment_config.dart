/// Handles access to build-time environment variables
class EnvironmentConfig {
  const EnvironmentConfig._();

  // Sentry configuration
  static const String sentryDsn =
      String.fromEnvironment('SENTRY_DSN', defaultValue: '');
  static const String sentryEnv =
      String.fromEnvironment('SENTRY_ENV', defaultValue: '');
  static const bool debugUseSentry =
      bool.fromEnvironment('DEBUG_USE_SENTRY', defaultValue: false);

  // WordPress configuration
  static const String wpBaseUrl = String.fromEnvironment(
    'WP_BASE_URL',
    defaultValue: 'http://localhost:8008',
  );
}
