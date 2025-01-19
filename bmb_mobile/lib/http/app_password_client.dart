import 'package:bmb_mobile/auth/wp_credential_manager.dart';
import 'package:bmb_mobile/http/authenticated_http_client.dart';

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
