import 'package:bmb_mobile/features/wp_auth/data/repositories/wp_credential_repository.dart';
import 'package:bmb_mobile/features/wp_http/domain/service/wp_http_client.dart';

class WpAppPasswordClient extends WpHttpClient {
  final WpCredentialRepository credentialManager;

  const WpAppPasswordClient(this.credentialManager);

  @override
  Future<Map<String, String>?> getAuthHeaders() async {
    final credentials = await credentialManager.getStoredCredentials();
    if (credentials == null) return null;

    return {
      'Authorization': credentials.basicAuth,
    };
  }
}
