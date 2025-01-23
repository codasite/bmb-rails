import 'package:bmb_mobile/app/bmb_app.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:webview_cookie_manager/webview_cookie_manager.dart';
import 'firebase_options.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_native_splash/flutter_native_splash.dart';
import 'package:bmb_mobile/core/utils/app_logger.dart';
import 'package:flutter_dotenv/flutter_dotenv.dart';
import 'package:provider/provider.dart';
import 'package:bmb_mobile/features/wp_auth/presentation/providers/auth_provider.dart';
import 'package:bmb_mobile/features/wp_http/presentation/providers/wp_http_client_provider.dart'
    as http;
import 'package:bmb_mobile/features/notifications/presentation/providers/fcm_token_manager_provider.dart';
import 'package:bmb_mobile/features/wp_auth/data/repositories/wp_credential_repository.dart';
import 'package:bmb_mobile/features/wp_http/domain/service/wp_session_client.dart';
import 'package:bmb_mobile/features/wp_http/domain/service/wp_app_password_client.dart';
import 'package:bmb_mobile/features/wp_auth/domain/services/wp_basic_auth.dart';
import 'package:bmb_mobile/features/wp_auth/domain/services/wp_cookie_auth.dart';
import 'package:bmb_mobile/features/wp_auth/domain/services/wp_auth.dart';
import 'package:bmb_mobile/features/notifications/domain/services/fcm_token_manager.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:firebase_messaging/firebase_messaging.dart';

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
  final credentialManager = WpCredentialRepository();
  final sessionClient = WpSessionClient(cookieManager);
  final passwordClient = WpAppPasswordClient(credentialManager);
  final basicAuth = WpBasicAuth(
    passwordClient,
    sessionClient,
    credentialManager,
  );
  final cookieAuth = WpCookieAuth(cookieManager);
  final auth = WpAuth(cookieAuth, basicAuth);
  final prefs = await SharedPreferences.getInstance();
  final fcmManager = FcmTokenManager(
    passwordClient,
    FirebaseMessaging.instance,
    prefs,
  );
  await auth.refreshAuthStatus();
  final httpProvider = http.WpHttpClientProvider(
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
