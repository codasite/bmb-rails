import 'package:bmb_mobile/core/theme/bmb_colors.dart';
import 'package:bmb_mobile/features/notifications/data/models/bmb_notification.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

class NotificationItem extends StatelessWidget {
  final BmbNotification notification;
  final VoidCallback onDelete;
  final Function(String) onMarkAsRead;

  const NotificationItem({
    super.key,
    required this.notification,
    required this.onDelete,
    required this.onMarkAsRead,
  });

  String _formatTimestamp(DateTime timestamp) {
    final now = DateTime.now();
    final difference = now.difference(timestamp);

    if (difference.inMinutes < 60) {
      return '${difference.inMinutes}m ago';
    } else if (difference.inHours < 24) {
      return '${difference.inHours}h ago';
    } else if (difference.inDays < 7) {
      return '${difference.inDays}d ago';
    } else {
      return DateFormat('MMM d, y').format(timestamp);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(
        horizontal: 16,
        vertical: 8,
      ),
      child: Dismissible(
        key: Key(notification.id),
        direction: DismissDirection.endToStart,
        background: Container(
          decoration: BoxDecoration(
            color: Colors.red,
            borderRadius: BorderRadius.circular(12),
          ),
          child: Container(
            alignment: Alignment.centerRight,
            padding: const EdgeInsets.only(right: 16),
            child: const Icon(
              Icons.delete,
              color: Colors.white,
            ),
          ),
        ),
        onDismissed: (_) => onDelete(),
        child: Card(
          margin: const EdgeInsets.symmetric(
            horizontal: 0,
            vertical: 0,
          ),
          color: notification.isRead
              ? BmbColors.darkBlue
              : BmbColors.blue.withOpacity(0.9),
          child: InkWell(
            onTap: () {
              if (!notification.isRead) {
                onMarkAsRead(notification.id);
              }
              if (notification.link != null) {
                Navigator.pop(context, notification.link);
              }
            },
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Text(
                          notification.title,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                      Text(
                        _formatTimestamp(notification.timestamp),
                        style: TextStyle(
                          color: Colors.white.withOpacity(0.7),
                          fontSize: 12,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    notification.message,
                    style: TextStyle(
                      color: Colors.white.withOpacity(0.9),
                      fontSize: 14,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
