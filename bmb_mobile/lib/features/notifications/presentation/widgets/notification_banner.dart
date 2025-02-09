import 'package:bmb_mobile/features/notifications/data/models/bmb_notification.dart';
import 'package:bmb_mobile/features/notifications/presentation/providers/notification_provider.dart';
import 'package:flutter/material.dart';
import 'package:bmb_mobile/core/theme/bmb_colors.dart';
import 'package:provider/provider.dart';

class NotificationBanner extends StatelessWidget {
  final BmbNotification notification;
  final VoidCallback onDismiss;
  final Future<void> Function(String, {bool prependBaseUrl}) onLoadUrl;

  const NotificationBanner({
    super.key,
    required this.notification,
    required this.onDismiss,
    required this.onLoadUrl,
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
                  notification.title,
                  style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: 16,
                    color: BmbColors.darkBlue,
                  ),
                ),
                if (notification.message.isNotEmpty) ...[
                  const SizedBox(height: 4),
                  Text(
                    notification.message,
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
                if (notification.id != null) {
                  context
                      .read<NotificationProvider>()
                      .markAsRead(notification.id!);
                }
                if (notification.link != null) {
                  await onLoadUrl(notification.link!, prependBaseUrl: false);
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
