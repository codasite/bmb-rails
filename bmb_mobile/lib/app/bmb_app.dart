import 'package:bmb_mobile/features/notifications/presentation/widgets/fcm_lifecycle_manager.dart';
import 'package:bmb_mobile/features/web_view/presentation/screens/web_view_screen.dart';
import 'package:flutter/material.dart';
import 'package:bmb_mobile/features/wp_auth/presentation/screens/login_screen.dart';
import 'package:flutter_native_splash/flutter_native_splash.dart';
import 'package:provider/provider.dart';
import 'package:bmb_mobile/features/wp_auth/presentation/providers/auth_provider.dart';

class BmbApp extends StatelessWidget {
  const BmbApp({super.key});

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
        '/app': (context) => const FCMLifecycleManager(child: WebViewScreen()),
        '/login': (context) => const LoginScreen(),
      },
      initialRoute:
          context.read<AuthProvider>().isAuthenticated ? '/app' : '/login',
    );
  }
}
