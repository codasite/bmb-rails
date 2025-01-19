import 'package:flutter/foundation.dart';
import 'package:bmb_mobile/features/auth/data/repositories/wp_credential_repository.dart';
import 'package:bmb_mobile/features/http/domain/service/session_http_client.dart';
import 'package:bmb_mobile/features/http/domain/service/app_password_http_client.dart';

class HttpClientProvider with ChangeNotifier {
  final SessionHttpClient _sessionClient;
  final AppPasswordHttpClient _passwordClient;

  HttpClientProvider({
    required WpCredentialRepository credentialManager,
    required SessionHttpClient sessionClient,
    required AppPasswordHttpClient passwordClient,
  })  : _sessionClient = sessionClient,
        _passwordClient = passwordClient;

  SessionHttpClient get sessionClient => _sessionClient;
  AppPasswordHttpClient get passwordClient => _passwordClient;
}
