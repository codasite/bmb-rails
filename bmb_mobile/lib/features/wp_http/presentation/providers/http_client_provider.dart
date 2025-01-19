import 'package:flutter/foundation.dart';
import 'package:bmb_mobile/features/wp_auth/data/repositories/wp_credential_repository.dart';
import 'package:bmb_mobile/features/wp_http/domain/service/wp_session_client.dart';
import 'package:bmb_mobile/features/wp_http/domain/service/wp_app_password_client.dart';

class HttpClientProvider with ChangeNotifier {
  final WpSessionClient _sessionClient;
  final WpAppPasswordClient _passwordClient;

  HttpClientProvider({
    required WpCredentialRepository credentialManager,
    required WpSessionClient sessionClient,
    required WpAppPasswordClient passwordClient,
  })  : _sessionClient = sessionClient,
        _passwordClient = passwordClient;

  WpSessionClient get sessionClient => _sessionClient;
  WpAppPasswordClient get passwordClient => _passwordClient;
}
