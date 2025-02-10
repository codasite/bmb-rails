import 'package:bmb_mobile/core/theme/bmb_colors.dart';
import 'package:bmb_mobile/core/theme/bmb_font_weights.dart';
import 'package:bmb_mobile/features/notifications/data/models/bmb_notification.dart';
import 'package:bmb_mobile/features/notifications/presentation/providers/notification_provider.dart';
import 'package:bmb_mobile/features/notifications/presentation/widgets/mark_all_as_read_button.dart';
import 'package:bmb_mobile/features/notifications/presentation/widgets/notification_item.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:bmb_mobile/core/widgets/upper_case_text.dart';

class NotificationScreen extends StatefulWidget {
  const NotificationScreen({super.key});

  @override
  State<NotificationScreen> createState() => _NotificationScreenState();
}

class _NotificationScreenState extends State<NotificationScreen> {
  void _handleNotificationTap(BmbNotification notification) {
    if (!notification.isRead && notification.id != null) {
      context.read<NotificationProvider>().markAsRead(notification.id!);
    }
    if (notification.link != null) {
      Navigator.pushReplacementNamed(context, '/app',
          arguments: notification.link);
    }
  }

  void _handleNotificationDismiss(BmbNotification notification) {
    context.read<NotificationProvider>().deleteNotification(notification.id!);
  }

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<NotificationProvider>().fetchNotifications();
    });
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<NotificationProvider>();
    final notifications = provider.notifications;
    final hasUnread = provider.unreadCount > 0;

    return Scaffold(
      backgroundColor: BmbColors.ddBlue,
      appBar: AppBar(
        backgroundColor: BmbColors.ddBlue,
        title: UpperCaseText(
          'Notifications',
          style: TextStyle(
            color: Colors.white,
            fontSize: 16,
            fontVariations: BmbFontWeights.w500,
          ),
        ),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () => Navigator.of(context).pop(),
        ),
      ),
      body: RefreshIndicator(
        onRefresh: provider.fetchNotifications,
        child: notifications.isEmpty
            ? ListView(
                children: const [
                  Center(
                    child: Padding(
                      padding: EdgeInsets.only(top: 32),
                      child: Text(
                        'No notifications',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 16,
                        ),
                      ),
                    ),
                  ),
                ],
              )
            : ListView.separated(
                padding: const EdgeInsets.all(15),
                itemCount: notifications.length + (1),
                separatorBuilder: (context, index) =>
                    const SizedBox(height: 15),
                itemBuilder: (context, index) {
                  if (index == 0) {
                    return Align(
                      alignment: Alignment.centerRight,
                      child: MarkAllAsReadButton(
                        hasUnread: hasUnread,
                        onPressed: provider.markAllAsRead,
                      ),
                    );
                  }
                  final notification = notifications[index - 1];
                  return NotificationItem(
                    notification: notification,
                    onDismiss: () => _handleNotificationDismiss(notification),
                    onTap: () => _handleNotificationTap(notification),
                  );
                },
              ),
      ),
    );
  }
}
