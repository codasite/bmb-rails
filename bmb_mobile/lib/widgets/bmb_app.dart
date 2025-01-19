import 'package:bmb_mobile/widgets/web_view_wrapper.dart';
import 'package:flutter/material.dart';
import 'package:bmb_mobile/login/login_screen.dart';
import 'package:flutter_native_splash/flutter_native_splash.dart';
import 'package:provider/provider.dart';
import 'package:bmb_mobile/providers/auth_provider.dart';

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
        '/app': (context) => const WebViewWrapper(),
        '/login': (context) => const LoginScreen(),
      },
      initialRoute:
          context.read<AuthProvider>().isAuthenticated ? '/app' : '/login',
    );
  }
}
