import 'package:bmb_mobile/bmb_app.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:webview_cookie_manager/webview_cookie_manager.dart';
import 'firebase_options.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_native_splash/flutter_native_splash.dart';
import 'package:bmb_mobile/utils/app_logger.dart';
import 'package:flutter_dotenv/flutter_dotenv.dart';
import 'package:provider/provider.dart';
import 'package:bmb_mobile/providers/auth_provider.dart';
import 'package:bmb_mobile/providers/http_client_provider.dart' as http;
import 'package:bmb_mobile/providers/fcm_token_manager_provider.dart';
import 'package:bmb_mobile/auth/wp_credential_manager.dart';
import 'package:bmb_mobile/http/session_http_client.dart';
import 'package:bmb_mobile/http/app_password_http_client.dart';
import 'package:bmb_mobile/auth/wp_basic_auth.dart';
import 'package:bmb_mobile/auth/wp_cookie_auth.dart';
import 'package:bmb_mobile/auth/wp_auth.dart';
import 'package:bmb_mobile/firebase/fcm_token_manager.dart';

void main() async {
  WidgetsBinding widgetsBinding = WidgetsFlutterBinding.ensureInitialized();
  FlutterNativeSplash.preserve(widgetsBinding: widgetsBinding);

  // Load environment variables
  await dotenv.load();

  await AppLogger.initialize(
    dsn: dotenv.env['SENTRY_DSN'] ?? '',
    environment: dotenv.env['SENTRY_ENV'] ?? 'development',
  );

  await Firebase.initializeApp(
    options: DefaultFirebaseOptions.currentPlatform,
  );

  await SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
    DeviceOrientation.portraitDown,
  ]);

  final cookieManager = WebviewCookieManager();
  final credentialManager = WpCredentialManager();
  final sessionClient = SessionHttpClient(cookieManager);
  final passwordClient = AppPasswordHttpClient(credentialManager);
  final basicAuth = WpBasicAuth(
    passwordClient,
    sessionClient,
    credentialManager,
  );
  final cookieAuth = WpCookieAuth(cookieManager);
  final auth = WpAuth(cookieAuth, basicAuth);
  final fcmManager = FcmTokenManager(sessionClient);
  await auth.refreshAuthStatus();
  final httpProvider = http.HttpClientProvider(
    credentialManager: credentialManager,
    sessionClient: sessionClient,
    passwordClient: passwordClient,
  );
  final authProvider = AuthProvider(auth: auth);
  final fcmProvider = FCMTokenManagerProvider(
    fcmManager: fcmManager,
  );

  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider.value(value: httpProvider),
        ChangeNotifierProvider.value(value: authProvider),
        ChangeNotifierProvider.value(value: fcmProvider),
      ],
      child: const BmbApp(),
    ),
  );
}
