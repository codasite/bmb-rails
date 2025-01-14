import 'package:bmb_mobile/main.dart';
import 'package:flutter/material.dart';
import 'package:bmb_mobile/login/auth_service.dart';
import 'package:bmb_mobile/theme/bmb_colors.dart';
import 'package:bmb_mobile/theme/font_weights.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:bmb_mobile/constants.dart';
import 'package:flutter_svg/flutter_svg.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _authService = AuthService();
  bool _isLoading = false;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  void _handleLogin() async {
    if (_formKey.currentState!.validate()) {
      setState(() {
        _isLoading = true;
      });

      try {
        bool loginSuccess = await _authService.login(
          _emailController.text,
          _passwordController.text,
        );

        if (mounted) {
          setState(() {
            _isLoading = false;
          });
        }

        if (loginSuccess && mounted) {
          Navigator.pushReplacementNamed(context, '/app');
        } else if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text(
                  'Login failed. Please check your credentials and try again.'),
            ),
          );
        }
      } catch (e) {
        if (mounted) {
          setState(() {
            _isLoading = false;
          });
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Error: ${e.toString()}'),
            ),
          );
        }
      }
    }
  }

  void _handleSignUp() async {
    final Uri url = Uri.parse(AppConstants.baseUrl + AppConstants.registerPath);
    if (await canLaunchUrl(url)) {
      await launchUrl(url, mode: LaunchMode.externalApplication);
    }
  }

  void _handleForgotPassword() async {
    final Uri url =
        Uri.parse(AppConstants.baseUrl + AppConstants.lostPasswordPath);
    if (await canLaunchUrl(url)) {
      await launchUrl(url, mode: LaunchMode.externalApplication);
    }
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
                          // height will adjust automatically to maintain aspect ratio
                        ),
                        const SizedBox(height: 30),
                        Text(
                          'RETURNING MEMBER',
                          style: TextStyle(
                            fontFamily: 'ClashDisplay',
                            fontSize: 32,
                            fontVariations: BMBFontWeight.w700,
                            color: Colors.white,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        const SizedBox(height: 30),
                        TextFormField(
                          controller: _emailController,
                          autofillHints: const [
                            AutofillHints.username,
                            AutofillHints.email
                          ],
                          style: const TextStyle(color: Colors.white),
                          decoration: InputDecoration(
                            labelText: 'EMAIL',
                            labelStyle: TextStyle(
                              color: Colors.white70,
                              fontSize: 16,
                              fontVariations: BMBFontWeight.w500,
                            ),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(8),
                              borderSide: const BorderSide(
                                color: BMBColors.blue,
                                width: 1,
                              ),
                            ),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(8),
                              borderSide: BorderSide(
                                color: BMBColors.blue.withOpacity(0.7),
                                width: 1,
                              ),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(8),
                              borderSide: const BorderSide(
                                color: BMBColors.blue,
                                width: 1,
                              ),
                            ),
                            prefixIcon:
                                const Icon(Icons.email, color: Colors.white70),
                          ),
                          keyboardType: TextInputType.emailAddress,
                          validator: (value) {
                            if (value == null || value.isEmpty) {
                              return 'Please enter your email';
                            }
                            // if (!value.contains('@')) {
                            //   return 'Please enter a valid email';
                            // }
                            return null;
                          },
                        ),
                        const SizedBox(height: 15),
                        TextFormField(
                          controller: _passwordController,
                          autofillHints: const [AutofillHints.password],
                          style: const TextStyle(color: Colors.white),
                          decoration: InputDecoration(
                            labelText: 'PASSWORD',
                            labelStyle: TextStyle(
                              color: Colors.white70,
                              fontSize: 16,
                              fontVariations: BMBFontWeight.w500,
                            ),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(8),
                              borderSide: const BorderSide(
                                color: BMBColors.blue,
                                width: 1,
                              ),
                            ),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(8),
                              borderSide: BorderSide(
                                color: BMBColors.blue.withOpacity(0.7),
                                width: 1,
                              ),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(8),
                              borderSide: const BorderSide(
                                color: BMBColors.blue,
                                width: 1,
                              ),
                            ),
                            prefixIcon:
                                const Icon(Icons.lock, color: Colors.white70),
                          ),
                          obscureText: true,
                          validator: (value) {
                            if (value == null || value.isEmpty) {
                              return 'Please enter your password';
                            }
                            // if (value.length < 6) {
                            //   return 'Password must be at least 6 characters';
                            // }
                            return null;
                          },
                        ),
                        Align(
                          alignment: Alignment.centerLeft,
                          child: TextButton(
                            onPressed: _handleForgotPassword,
                            style: TextButton.styleFrom(
                              tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                            ),
                            child: Text(
                              'FORGOT PASSWORD?',
                              style: TextStyle(
                                fontSize: 14,
                                color: Colors.white.withOpacity(0.5),
                                fontVariations: BMBFontWeight.w500,
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(height: 15),
                        ElevatedButton(
                          onPressed: _isLoading ? null : _handleLogin,
                          style: ElevatedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            backgroundColor: BMBColors.blue.withOpacity(0.30),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(8),
                              side: const BorderSide(
                                color: BMBColors.blue,
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
                                  'SIGN IN',
                                  style: TextStyle(
                                    fontSize: 16,
                                    color: Colors.white,
                                    fontVariations: BMBFontWeight.w500,
                                  ),
                                ),
                        ),
                        TextButton(
                          onPressed: _handleSignUp,
                          child: Text(
                            'OR SIGN UP',
                            style: TextStyle(
                              fontSize: 14,
                              color: Colors.white.withOpacity(0.5),
                              fontVariations: BMBFontWeight.w500,
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
