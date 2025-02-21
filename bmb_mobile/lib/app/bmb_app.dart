import 'package:bmb_mobile/core/utils/app_logger.dart';
import 'package:bmb_mobile/features/app_links/presentation/providers/app_link_provider.dart';
import 'package:bmb_mobile/features/notifications/presentation/widgets/fcm_lifecycle_manager.dart';
import 'package:bmb_mobile/features/webview/presentation/screens/webview_screen.dart';
import 'package:flutter/material.dart';
import 'package:bmb_mobile/features/wp_auth/presentation/screens/login_screen.dart';
import 'package:bmb_mobile/features/wp_auth/presentation/screens/register_screen.dart';
import 'package:bmb_mobile/features/wp_auth/presentation/screens/forgot_password_screen.dart';
import 'package:flutter_native_splash/flutter_native_splash.dart';
import 'package:provider/provider.dart';
import 'package:bmb_mobile/features/wp_auth/presentation/providers/auth_provider.dart';
import 'package:bmb_mobile/features/wp_http/wp_urls.dart';

class BmbApp extends StatelessWidget {
  const BmbApp({super.key});

  String _getInitialRoute(BuildContext context) {
    AppLogger.debugLog('Getting initial route');
    final appLink = context.read<AppLinkProvider>().getUri();
    if (appLink != null) {
      AppLogger.debugLog('app link found: $appLink');
      AppLogger.debugLog('app link path: ${appLink.path}');
      if (appLink.path.contains(WpUrls.resetPasswordPath)) {
        AppLogger.debugLog(
            'reset password link found. navigating to reset password screen');
        context.read<AuthProvider>().logout();
        return '/reset-password';
      }
    }
    final authProvider = context.read<AuthProvider>();
    if (authProvider.isAuthenticated) {
      return '/app';
    }
    return '/login';
  }

  @override
  Widget build(BuildContext context) {
    Future.delayed(const Duration(seconds: 1), () {
      FlutterNativeSplash.remove();
    });

    return MaterialApp(
      title: 'Back My Bracket',
      theme: ThemeData(
        fontFamily: 'ClashDisplay',
      ),
      routes: {
        '/app': (context) => const FCMLifecycleManager(
              child: WebViewScreen(),
            ),
        '/login': (context) => const LoginScreen(),
        '/register': (context) => const RegisterScreen(),
        '/forgot-password': (context) => const ForgotPasswordScreen(),
        // '/reset-password': (context) => const ResetPasswordScreen(),
      },
      initialRoute: _getInitialRoute(context),
    );
  }
}
