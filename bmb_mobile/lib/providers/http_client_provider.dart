import 'package:flutter/foundation.dart';
import 'package:bmb_mobile/auth/wp_credential_manager.dart';
import 'package:bmb_mobile/http/session_http_client.dart';
import 'package:bmb_mobile/http/app_password_http_client.dart';

class HttpClientProvider with ChangeNotifier {
  final SessionHttpClient _sessionClient;
  final AppPasswordHttpClient _passwordClient;

  HttpClientProvider({
    required WpCredentialManager credentialManager,
    required SessionHttpClient sessionClient,
    required AppPasswordHttpClient passwordClient,
  })  : _sessionClient = sessionClient,
        _passwordClient = passwordClient;

  SessionHttpClient get sessionClient => _sessionClient;
  AppPasswordHttpClient get passwordClient => _passwordClient;
}
