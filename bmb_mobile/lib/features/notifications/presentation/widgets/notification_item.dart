import 'package:bmb_mobile/core/theme/bmb_colors.dart';
import 'package:bmb_mobile/core/theme/bmb_font_weights.dart';
import 'package:bmb_mobile/features/notifications/data/models/bmb_notification.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

class NotificationItem extends StatelessWidget {
  final BmbNotification notification;
  final Function(int) onDelete;
  final Function(int) onMarkAsRead;
  final Function(String?) onTap;

  const NotificationItem({
    super.key,
    required this.notification,
    required this.onDelete,
    required this.onMarkAsRead,
    required this.onTap,
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
    final notification = this.notification;
    return Dismissible(
      key: Key(notification.id.toString()),
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
      onDismissed: (_) =>
          notification.id != null ? onDelete(notification.id!) : null,
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(12),
          border: !notification.isRead
              ? Border.all(
                  color: BmbColors.blue,
                  width: 2,
                )
              : Border.all(
                  color: BmbColors.white.withOpacity(0.15),
                  width: 2,
                ),
        ),
        child: Material(
          type: MaterialType.transparency,
          child: InkWell(
            onTap: () {
              if (!notification.isRead && notification.id != null) {
                onMarkAsRead(notification.id!);
              }
              onTap(notification.link);
            },
            borderRadius: BorderRadius.circular(12),
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
                          style: TextStyle(
                            color: notification.isRead
                                ? Colors.white.withOpacity(0.7)
                                : Colors.white,
                            fontSize: 16,
                            fontVariations: BmbFontWeights.w500,
                          ),
                        ),
                      ),
                      if (notification.timestamp != null)
                        Text(
                          _formatTimestamp(notification.timestamp!),
                          style: TextStyle(
                            color: Colors.white.withOpacity(0.5),
                            fontSize: 12,
                            fontVariations: BmbFontWeights.w500,
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  if (notification.message.isNotEmpty)
                    Text(
                      notification.message,
                      style: TextStyle(
                        color: notification.isRead
                            ? Colors.white.withOpacity(0.5)
                            : Colors.white.withOpacity(0.7),
                        fontSize: 14,
                        fontVariations: BmbFontWeights.w500,
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
