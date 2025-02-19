import 'package:flutter/material.dart';
import 'package:bmb_mobile/core/theme/bmb_colors.dart';
import 'package:bmb_mobile/core/theme/bmb_font_weights.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:bmb_mobile/features/wp_http/wp_urls.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:bmb_mobile/features/wp_auth/presentation/providers/auth_provider.dart';
import 'package:provider/provider.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  bool _isLoading = false;
  bool _registrationComplete = false;

  @override
  void dispose() {
    _emailController.dispose();
    super.dispose();
  }

  Future<void> _register() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    try {
      final success = await context.read<AuthProvider>().register(
            _emailController.text,
            '', // Empty password since WordPress will email it
          );

      if (success && mounted) {
        setState(() => _registrationComplete = true);
      } else if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Registration failed')),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  void _handleSignIn() {
    Navigator.pop(context);
  }

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
                child: Form(
                  key: _formKey,
                  child: AutofillGroup(
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
                          'JOIN THE TEAM',
                          style: TextStyle(
                            fontFamily: 'ClashDisplay',
                            fontSize: 32,
                            fontVariations: BmbFontWeights.w700,
                            color: Colors.white,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 30),
                        if (!_registrationComplete) ...[
                          TextFormField(
                            controller: _emailController,
                            autofillHints: const [AutofillHints.email],
                            style: const TextStyle(color: Colors.white),
                            decoration: InputDecoration(
                              labelText: 'EMAIL',
                              labelStyle: TextStyle(
                                color: Colors.white70,
                                fontSize: 16,
                                fontVariations: BmbFontWeights.w500,
                              ),
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(8),
                                borderSide: const BorderSide(
                                  color: BmbColors.blue,
                                  width: 1,
                                ),
                              ),
                              enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(8),
                                borderSide: BorderSide(
                                  color: BmbColors.blue.withOpacity(0.7),
                                  width: 1,
                                ),
                              ),
                              focusedBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(8),
                                borderSide: const BorderSide(
                                  color: BmbColors.blue,
                                  width: 1,
                                ),
                              ),
                              prefixIcon: const Icon(Icons.email,
                                  color: Colors.white70),
                            ),
                            keyboardType: TextInputType.emailAddress,
                            validator: (value) {
                              if (value == null || value.isEmpty) {
                                return 'Please enter your email';
                              }
                              if (!value.contains('@')) {
                                return 'Please enter a valid email';
                              }
                              return null;
                            },
                          ),
                          const SizedBox(height: 30),
                          ElevatedButton(
                            onPressed: _isLoading ? null : _register,
                            style: ElevatedButton.styleFrom(
                              padding: const EdgeInsets.symmetric(vertical: 16),
                              backgroundColor: BmbColors.blue.withOpacity(0.30),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(8),
                                side: const BorderSide(
                                  color: BmbColors.blue,
                                  width: 1,
                                ),
                              ),
                            ),
                            child: _isLoading
                                ? const SizedBox(
                                    height: 20,
                                    width: 20,
                                    child: CircularProgressIndicator(
                                      strokeWidth: 2,
                                      valueColor: AlwaysStoppedAnimation<Color>(
                                          Colors.white),
                                    ),
                                  )
                                : Text(
                                    'CREATE ACCOUNT',
                                    style: TextStyle(
                                      fontSize: 16,
                                      color: Colors.white,
                                      fontVariations: BmbFontWeights.w500,
                                    ),
                                  ),
                          ),
                        ] else ...[
                          Text(
                            'Check your email',
                            style: TextStyle(
                              fontSize: 24,
                              color: Colors.white,
                              fontVariations: BmbFontWeights.w700,
                            ),
                            textAlign: TextAlign.center,
                          ),
                          const SizedBox(height: 20),
                          Text(
                            'We\'ve sent your login details to ${_emailController.text}',
                            style: TextStyle(
                              fontSize: 16,
                              color: Colors.white.withOpacity(0.7),
                              fontVariations: BmbFontWeights.w500,
                            ),
                            textAlign: TextAlign.center,
                          ),
                          const SizedBox(height: 30),
                        ],
                        TextButton(
                          onPressed: _handleSignIn,
                          child: Text(
                            'ALREADY HAVE AN ACCOUNT? SIGN IN',
                            style: TextStyle(
                              fontSize: 14,
                              color: Colors.white.withOpacity(0.5),
                              fontVariations: BmbFontWeights.w500,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
