import 'package:flutter/material.dart';
import 'package:bmb_mobile/core/theme/bmb_colors.dart';
import 'package:bmb_mobile/core/theme/bmb_font_weights.dart';
import 'package:bmb_mobile/features/wp_auth/presentation/providers/auth_provider.dart';
import 'package:provider/provider.dart';
import 'package:bmb_mobile/features/wp_auth/presentation/widgets/auth_screen_layout.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _usernameController = TextEditingController();
  bool _isLoading = false;
  bool _registrationComplete = false;
  String? _emailError;
  String? _usernameError;

  @override
  void dispose() {
    _emailController.dispose();
    _usernameController.dispose();
    super.dispose();
  }

  Future<void> _register() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
      _emailError = null;
      _usernameError = null;
    });

    try {
      final success = await context.read<AuthProvider>().register(
            _emailController.text,
            _usernameController.text,
          );

      if (success && mounted) {
        setState(() => _registrationComplete = true);
      } else if (mounted) {
        final errors = context.read<AuthProvider>().getErrorsList();
        final otherErrors = <String>[];

        for (final error in errors) {
          final lowerError = error.toLowerCase();
          if (lowerError.contains('email')) {
            setState(() => _emailError = error);
          } else if (lowerError.contains('username')) {
            setState(() => _usernameError = error);
          } else {
            otherErrors.add(error);
          }
        }

        if (otherErrors.isNotEmpty && mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(otherErrors.join('\n')),
              backgroundColor: Colors.red,
              duration: const Duration(seconds: 5),
            ),
          );
        }
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
    final formFields = [
      if (!_registrationComplete) ...[
        TextFormField(
          controller: _usernameController,
          autofillHints: const [AutofillHints.username],
          style: const TextStyle(color: Colors.white),
          decoration: InputDecoration(
            labelText: 'USERNAME',
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
                color: BmbColors.blue.withValues(alpha: 0.7),
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
            errorText: _usernameError?.split('.')[0],
            errorStyle: const TextStyle(
              color: Colors.red,
              fontSize: 12,
            ),
            prefixIcon: const Icon(Icons.person, color: Colors.white70),
          ),
          validator: (value) {
            if (value == null || value.isEmpty) {
              return 'Please enter a username';
            }
            if (value.length < 3) {
              return 'Username must be at least 3 characters';
            }
            return null;
          },
        ),
        const SizedBox(height: 20),
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
                color: BmbColors.blue.withValues(alpha: 0.7),
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
            errorText: _emailError?.split('.')[0],
            errorStyle: const TextStyle(
              color: Colors.red,
              fontSize: 12,
            ),
            prefixIcon: const Icon(Icons.email, color: Colors.white70),
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
            backgroundColor: BmbColors.blue.withValues(alpha: 0.30),
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
                    valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
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
            color: Colors.white.withValues(alpha: 0.7),
            fontVariations: BmbFontWeights.w500,
          ),
          textAlign: TextAlign.center,
        ),
        const SizedBox(height: 30),
      ],
    ];

    return Form(
      key: _formKey,
      child: AuthScreenLayout(
        title: 'JOIN THE TEAM',
        bottomButton: TextButton(
          onPressed: _handleSignIn,
          child: Text(
            'ALREADY HAVE AN ACCOUNT? SIGN IN',
            style: TextStyle(
              fontSize: 14,
              color: Colors.white.withValues(alpha: 0.5),
              fontVariations: BmbFontWeights.w500,
            ),
          ),
        ),
        children: formFields,
      ),
    );
  }
}
