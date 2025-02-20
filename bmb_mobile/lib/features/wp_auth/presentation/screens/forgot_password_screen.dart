import 'package:flutter/material.dart';
import 'package:bmb_mobile/core/theme/bmb_colors.dart';
import 'package:bmb_mobile/core/theme/bmb_font_weights.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:bmb_mobile/features/wp_auth/presentation/providers/auth_provider.dart';
import 'package:provider/provider.dart';
import 'package:bmb_mobile/features/wp_auth/presentation/widgets/auth_screen_layout.dart';

class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  bool _isLoading = false;
  bool _resetEmailSent = false;

  @override
  void dispose() {
    _emailController.dispose();
    super.dispose();
  }

  Future<void> _requestPasswordReset() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    try {
      final success = await context.read<AuthProvider>().requestPasswordReset(
            _emailController.text,
          );

      if (success && mounted) {
        setState(() => _resetEmailSent = true);
      } else if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Failed to send password reset email')),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  void _handleBackToLogin() {
    Navigator.pop(context);
  }

  @override
  Widget build(BuildContext context) {
    final formFields = [
      if (!_resetEmailSent) ...[
        Text(
          'Enter your email address and we\'ll send you instructions to reset your password.',
          style: TextStyle(
            fontSize: 16,
            color: Colors.white.withOpacity(0.7),
            fontVariations: BmbFontWeights.w500,
          ),
          textAlign: TextAlign.center,
        ),
        const SizedBox(height: 30),
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
          onPressed: _isLoading ? null : _requestPasswordReset,
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
                    valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                  ),
                )
              : Text(
                  'RESET PASSWORD',
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
          'We\'ve sent password reset instructions to ${_emailController.text}',
          style: TextStyle(
            fontSize: 16,
            color: Colors.white.withOpacity(0.7),
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
        title: 'RESET PASSWORD',
        children: formFields,
        bottomButton: TextButton(
          onPressed: _handleBackToLogin,
          child: Text(
            'BACK TO SIGN IN',
            style: TextStyle(
              fontSize: 14,
              color: Colors.white.withOpacity(0.5),
              fontVariations: BmbFontWeights.w500,
            ),
          ),
        ),
      ),
    );
  }
}
