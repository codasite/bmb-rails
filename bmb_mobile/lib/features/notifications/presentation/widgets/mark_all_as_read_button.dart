import 'package:bmb_mobile/core/theme/bmb_colors.dart';
import 'package:bmb_mobile/core/theme/bmb_font_weights.dart';
import 'package:flutter/material.dart';

class MarkAllAsReadButton extends StatelessWidget {
  final VoidCallback onPressed;

  const MarkAllAsReadButton({
    super.key,
    required this.onPressed,
  });

  @override
  Widget build(BuildContext context) {
    return FilledButton(
      onPressed: onPressed,
      style: FilledButton.styleFrom(
        backgroundColor: BmbColors.darkBlue,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(8),
        ),
      ),
      child: Text(
        'MARK ALL AS READ',
        style: TextStyle(
          color: BmbColors.white.withOpacity(0.5),
          fontSize: 12,
          fontVariations: BmbFontWeights.w500,
        ),
      ),
    );
  }
}
