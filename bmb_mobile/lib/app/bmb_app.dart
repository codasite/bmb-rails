import 'package:bmb_mobile/core/utils/app_logger.dart';
import 'package:bmb_mobile/features/app_links/presentation/providers/app_link_provider.dart';
import 'package:bmb_mobile/features/notifications/presentation/widgets/fcm_lifecycle_manager.dart';
import 'package:bmb_mobile/features/webview/presentation/screens/webview_screen.dart';
import 'package:flutter/material.dart';
import 'package:bmb_mobile/features/wp_auth/presentation/screens/login_screen.dart';
import 'package:bmb_mobile/features/wp_auth/presentation/screens/register_screen.dart';
import 'package:bmb_mobile/features/wp_auth/presentation/screens/forgot_password_screen.dart';
import 'package:bmb_mobile/features/wp_auth/presentation/screens/reset_password_screen.dart';
import 'package:flutter_native_splash/flutter_native_splash.dart';
import 'package:provider/provider.dart';
import 'package:bmb_mobile/features/wp_auth/presentation/providers/auth_provider.dart';
import 'package:bmb_mobile/features/wp_http/wp_urls.dart';

class BmbApp extends StatefulWidget {
  const BmbApp({super.key});

  @override
  State<BmbApp> createState() => _BmbAppState();
}

class _BmbAppState extends State<BmbApp> {
  final _navigatorKey = GlobalKey<NavigatorState>();

  @override
  void initState() {
    super.initState();
    Future.delayed(const Duration(seconds: 1), () {
      FlutterNativeSplash.remove();
    });
  }

  bool _shouldNavigateToResetPassword(Uri? appLink) {
    if (appLink != null && appLink.toString().contains(WpUrls.rpPath)) {
      AppLogger.debugLog('Reset password link found');
      return true;
    }
    return false;
  }

  String _getInitialRoute(BuildContext context) {
    AppLogger.debugLog('Getting initial route');
    final appLink = context.read<AppLinkProvider>().getUri();
    if (appLink != null) {
      AppLogger.debugLog('app link found: $appLink');
      AppLogger.debugLog('app link path: ${appLink.path}');
      if (_shouldNavigateToResetPassword(appLink)) {
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
    final appLink = context.watch<AppLinkProvider>().getUri();
    AppLogger.debugLog('in bmb_app app link: $appLink');

    if (_shouldNavigateToResetPassword(appLink)) {
      Future.microtask(() {
        _navigatorKey.currentState?.pushReplacementNamed('/reset-password');
      });
    }

    return MaterialApp(
      navigatorKey: _navigatorKey,
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
        '/reset-password': (context) => const ResetPasswordScreen(),
      },
      initialRoute: _getInitialRoute(context),
    );
  }
}
