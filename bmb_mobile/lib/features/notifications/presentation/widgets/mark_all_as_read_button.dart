import 'package:bmb_mobile/core/theme/bmb_colors.dart';
import 'package:bmb_mobile/core/theme/bmb_font_weights.dart';
import 'package:flutter/material.dart';

class MarkAllAsReadButton extends StatelessWidget {
  final bool hasUnread;
  final VoidCallback? onPressed;

  const MarkAllAsReadButton({
    super.key,
    required this.hasUnread,
    required this.onPressed,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(
        vertical: 8,
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.end,
        children: [
          Container(
            height: 44,
            decoration: BoxDecoration(
              color: hasUnread ? BmbColors.blue : BmbColors.darkBlue,
              borderRadius: BorderRadius.circular(12),
            ),
            child: TextButton(
              onPressed: onPressed,
              style: TextButton.styleFrom(
                padding: const EdgeInsets.symmetric(horizontal: 24),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    'MARK ALL AS READ',
                    style: TextStyle(
                      color: hasUnread
                          ? Colors.white
                          : Colors.white.withOpacity(0.5),
                      fontSize: 14,
                      fontVariations: BmbFontWeights.w500,
                    ),
                  ),
                  if (hasUnread) ...[
                    const SizedBox(width: 8),
                    const Icon(
                      Icons.check,
                      color: Colors.white,
                      size: 18,
                    ),
                  ],
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
