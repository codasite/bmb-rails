import 'package:flutter/foundation.dart';
import 'package:webview_cookie_manager/webview_cookie_manager.dart';
import 'package:bmb_mobile/auth/wp_credential_manager.dart';
import 'package:bmb_mobile/http/session_http_client.dart';
import 'package:bmb_mobile/http/app_password_http_client.dart';

class HttpClientProvider with ChangeNotifier {
  late final WpCredentialManager _credentialManager;
  late final SessionHttpClient _sessionClient;
  late final AppPasswordHttpClient _passwordClient;

  HttpClientProvider(WebviewCookieManager cookieManager) {
    _credentialManager = WpCredentialManager();
    _sessionClient = SessionHttpClient(cookieManager);
    _passwordClient = AppPasswordHttpClient(_credentialManager);
  }

  SessionHttpClient get sessionClient => _sessionClient;
  AppPasswordHttpClient get passwordClient => _passwordClient;
  WpCredentialManager get credentialManager => _credentialManager;
}
