import 'package:bmb_mobile/core/utils/app_logger.dart';
import 'package:flutter/material.dart';
import 'package:bmb_mobile/core/theme/bmb_colors.dart';
import 'package:bmb_mobile/core/theme/bmb_font_weights.dart';
import 'package:bmb_mobile/features/wp_auth/presentation/providers/auth_provider.dart';
import 'package:provider/provider.dart';
import 'package:bmb_mobile/features/wp_auth/presentation/widgets/auth_screen_layout.dart';
import 'package:bmb_mobile/features/app_links/presentation/providers/app_link_provider.dart';

class ResetPasswordScreen extends StatefulWidget {
  const ResetPasswordScreen({super.key});

  @override
  State<ResetPasswordScreen> createState() => _ResetPasswordScreenState();
}

class _ResetPasswordScreenState extends State<ResetPasswordScreen> {
  final _formKey = GlobalKey<FormState>();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  bool _isLoading = false;
  bool _resetComplete = false;
  bool _invalidLink = false;
  String? _passwordError;
  String? _resetKey;

  @override
  void initState() {
    super.initState();
    _validateResetLink();
  }

  @override
  void dispose() {
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  void _validateResetLink() async {
    final appLink = context.read<AppLinkProvider>().getAndClearUri();
    if (appLink == null) {
      setState(() => _invalidLink = true);
      return;
    }

    setState(() => _isLoading = true);
    try {
      final success =
          await context.read<AuthProvider>().validateResetPasswordLink(appLink);
      if (mounted) {
        if (success) {
          setState(() {
            _isLoading = false;
            _resetKey = appLink.queryParameters['key']!;
          });
        } else {
          setState(() {
            _isLoading = false;
            _invalidLink = true;
          });
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _invalidLink = true;
        });
      }
    }
  }

  Future<void> _resetPassword() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
      _passwordError = null;
    });

    try {
      if (_resetKey == null) {
        setState(() => _invalidLink = true);
        return;
      }

      final success = await context.read<AuthProvider>().resetPassword(
            _resetKey!,
            _passwordController.text,
          );

      if (success && mounted) {
        setState(() => _resetComplete = true);
      } else if (mounted) {
        final errors = context.read<AuthProvider>().getErrorsList();
        final otherErrors = <String>[];

        for (final error in errors) {
          final lowerError = error.toLowerCase();
          if (lowerError.contains('password')) {
            setState(() => _passwordError = error);
          } else {
            otherErrors.add(error);
          }
        }

        if (otherErrors.isNotEmpty) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(otherErrors.join('\n')),
              backgroundColor: Colors.red,
              duration: const Duration(seconds: 5),
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('An error occurred. Please try again.'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  void _handleSignIn() {
    Navigator.pushReplacementNamed(context, '/login');
  }

  void _handleRegister() {
    Navigator.pushReplacementNamed(context, '/register');
  }

  @override
  Widget build(BuildContext context) {
    AppLogger.debugLog('in reset password screen');

    if (_isLoading && !_invalidLink && !_resetComplete) {
      return const AuthScreenLayout(
        title: 'RESET PASSWORD',
        children: [
          Center(
            child: CircularProgressIndicator(
              valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
            ),
          ),
        ],
      );
    }

    final formFields = [
      if (_invalidLink) ...[
        Text(
          'Invalid Reset Link',
          style: TextStyle(
            fontSize: 24,
            color: Colors.white,
            fontVariations: BmbFontWeights.w700,
          ),
          textAlign: TextAlign.center,
        ),
        const SizedBox(height: 20),
        Text(
          'This password reset link is invalid or has expired. Please try resetting your password again.',
          style: TextStyle(
            fontSize: 16,
            color: Colors.white.withValues(alpha: 0.7),
            fontVariations: BmbFontWeights.w500,
          ),
          textAlign: TextAlign.center,
        ),
        const SizedBox(height: 30),
        ElevatedButton(
          onPressed: _handleSignIn,
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
          child: Text(
            'SIGN IN',
            style: TextStyle(
              fontSize: 16,
              color: Colors.white,
              fontVariations: BmbFontWeights.w500,
            ),
          ),
        ),
      ] else if (!_resetComplete) ...[
        Text(
          'Enter your new password below.',
          style: TextStyle(
            fontSize: 16,
            color: Colors.white.withValues(alpha: 0.7),
            fontVariations: BmbFontWeights.w500,
          ),
          textAlign: TextAlign.center,
        ),
        const SizedBox(height: 30),
        TextFormField(
          controller: _passwordController,
          autofillHints: const [AutofillHints.newPassword],
          style: const TextStyle(color: Colors.white),
          decoration: InputDecoration(
            labelText: 'NEW PASSWORD',
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
            errorText: _passwordError,
            errorStyle: const TextStyle(
              color: Colors.red,
              fontSize: 12,
            ),
            prefixIcon: const Icon(Icons.lock, color: Colors.white70),
          ),
          obscureText: true,
          validator: (value) {
            if (value == null || value.isEmpty) {
              return 'Please enter your new password';
            }
            if (value.length < 12) {
              return 'Password must be at least 12 characters';
            }
            return null;
          },
        ),
        const SizedBox(height: 15),
        TextFormField(
          controller: _confirmPasswordController,
          autofillHints: const [AutofillHints.newPassword],
          style: const TextStyle(color: Colors.white),
          decoration: InputDecoration(
            labelText: 'CONFIRM PASSWORD',
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
            prefixIcon: const Icon(Icons.lock, color: Colors.white70),
          ),
          obscureText: true,
          validator: (value) {
            if (value == null || value.isEmpty) {
              return 'Please confirm your new password';
            }
            if (value != _passwordController.text) {
              return 'Passwords do not match';
            }
            return null;
          },
        ),
        const SizedBox(height: 30),
        ElevatedButton(
          onPressed: _isLoading ? null : _resetPassword,
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
          'Password reset complete!',
          style: TextStyle(
            fontSize: 24,
            color: Colors.white,
            fontVariations: BmbFontWeights.w700,
          ),
          textAlign: TextAlign.center,
        ),
        const SizedBox(height: 20),
        Text(
          'Your password has been reset successfully. Please sign in with your new password.',
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
        title: 'RESET PASSWORD',
        bottomButton: TextButton(
          onPressed: _invalidLink ? _handleRegister : _handleSignIn,
          child: Text(
            _invalidLink
                ? 'DON\'T HAVE AN ACCOUNT? SIGN UP'
                : (_resetComplete ? 'SIGN IN' : 'BACK TO SIGN IN'),
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
