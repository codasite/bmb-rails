import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:bmb_mobile/core/theme/bmb_colors.dart';
import 'package:bmb_mobile/features/notifications/presentation/screens/notification_screen.dart';
import 'package:bmb_mobile/features/webview/presentation/providers/webview_provider.dart';

class NotificationBanner extends StatelessWidget {
  final RemoteMessage message;
  final VoidCallback onDismiss;

  const NotificationBanner({
    super.key,
    required this.message,
    required this.onDismiss,
  });

  @override
  Widget build(BuildContext context) {
    return MaterialBanner(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      elevation: 2,
      forceActionsBelow: true,
      content: Row(
        children: [
          Container(
            decoration: BoxDecoration(
              color: BmbColors.blue.withOpacity(0.1),
              borderRadius: BorderRadius.circular(8),
            ),
            padding: const EdgeInsets.all(8),
            child: const Icon(
              Icons.notifications_outlined,
              color: BmbColors.blue,
              size: 24,
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  message.notification?.title ?? 'New notification',
                  style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: 16,
                    color: BmbColors.darkBlue,
                  ),
                ),
                if (message.notification?.body != null) ...[
                  const SizedBox(height: 4),
                  Text(
                    message.notification!.body!,
                    style: TextStyle(
                      fontSize: 14,
                      color: BmbColors.darkBlue.withOpacity(0.8),
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
      backgroundColor: Colors.white,
      surfaceTintColor: Colors.white,
      actions: [
        Row(
          mainAxisAlignment: MainAxisAlignment.end,
          children: [
            TextButton(
              onPressed: onDismiss,
              style: TextButton.styleFrom(
                foregroundColor: BmbColors.darkBlue.withOpacity(0.7),
              ),
              child: const Text('DISMISS'),
            ),
            const SizedBox(width: 8),
            FilledButton(
              onPressed: () async {
                onDismiss();
                final result = await Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (context) => const NotificationScreen(),
                  ),
                );

                if (result != null && result is String && context.mounted) {
                  context
                      .read<WebViewProvider>()
                      .loadUrl(result, prependBaseUrl: false);
                }
              },
              style: FilledButton.styleFrom(
                backgroundColor: BmbColors.blue,
                foregroundColor: Colors.white,
              ),
              child: const Text('VIEW'),
            ),
          ],
        ),
      ],
    );
  }
}
