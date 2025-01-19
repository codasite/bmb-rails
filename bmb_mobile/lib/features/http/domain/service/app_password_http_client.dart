import 'package:bmb_mobile/features/auth/data/repositories/wp_credential_repository.dart';
import 'package:bmb_mobile/features/http/domain/service/authenticated_http_client.dart';

class AppPasswordHttpClient extends AuthenticatedHttpClient {
  final WpCredentialRepository credentialManager;

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
