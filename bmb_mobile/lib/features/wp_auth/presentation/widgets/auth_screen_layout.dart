import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:bmb_mobile/core/theme/bmb_colors.dart';
import 'package:bmb_mobile/core/theme/bmb_font_weights.dart';

class AuthScreenLayout extends StatelessWidget {
  final String title;
  final List<Widget> children;
  final Widget? bottomButton;

  const AuthScreenLayout({
    super.key,
    required this.title,
    required this.children,
    this.bottomButton,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        children: [
          // Background Image
          Image.asset(
            'assets/images/login_screen.png',
            width: double.infinity,
            fit: BoxFit.cover,
          ),
          // Gradient Container
          Container(
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [
                  Color.fromRGBO(0, 25, 255, 0.0),
                  Color(0xFF000857),
                  Color(0xFF000330),
                ],
                stops: [0.0, 0.555, 1.0],
              ),
            ),
            child: SafeArea(
              child: Padding(
                padding:
                    const EdgeInsets.symmetric(horizontal: 20.0, vertical: 20),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.end,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    SvgPicture.asset(
                      'assets/icons/bmb_logo.svg',
                      width: MediaQuery.of(context).size.width / 3,
                    ),
                    const SizedBox(height: 30),
                    Text(
                      title,
                      style: TextStyle(
                        fontFamily: 'ClashDisplay',
                        fontSize: 32,
                        fontVariations: BmbFontWeights.w700,
                        color: Colors.white,
                      ),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 30),
                    ...children,
                    if (bottomButton != null) bottomButton!,
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
