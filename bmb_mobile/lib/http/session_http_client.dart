import 'package:bmb_mobile/utils/app_logger.dart';
import 'package:bmb_mobile/constants.dart';
import 'package:webview_cookie_manager/webview_cookie_manager.dart';
import 'package:bmb_mobile/http/authenticated_http_client.dart';

class SessionHttpClient extends AuthenticatedHttpClient {
  final WebviewCookieManager cookieManager;

  const SessionHttpClient(this.cookieManager);

  @override
  Future<Map<String, String>?> getAuthHeaders() async {
    try {
      final baseUri = Uri.parse(AppConstants.baseUrl);
      final cookies = await cookieManager.getCookies(baseUri.toString());

      final restNonceCookie = cookies.firstWhere(
        (cookie) => cookie.name == 'wordpress_rest_nonce',
        orElse: () {
          throw Exception('WordPress REST nonce cookie not found');
        },
      );

      final nonce = restNonceCookie.value;
      final cookieHeader =
          cookies.map((c) => '${c.name}=${c.value}').join('; ');

      return {
        'X-WP-Nonce': nonce,
        'Cookie': cookieHeader,
      };
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Failed to generate auth headers'},
      );
      return null;
    }
  }
}
