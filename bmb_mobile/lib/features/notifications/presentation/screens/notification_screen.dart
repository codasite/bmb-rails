import 'package:bmb_mobile/core/theme/bmb_colors.dart';
import 'package:bmb_mobile/features/notifications/presentation/providers/notification_provider.dart';
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

    return Scaffold(
      backgroundColor: BmbColors.ddBlue,
      appBar: AppBar(
        backgroundColor: BmbColors.darkBlue,
        title: UpperCaseText(
          'Notifications',
          style: const TextStyle(color: Colors.white),
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
            : Column(
                children: [
                  if (provider.unreadCount > 0)
                    Padding(
                      padding: const EdgeInsets.only(top: 8, bottom: 8),
                      child: TextButton(
                        onPressed: () => provider.markAllAsRead(),
                        child: Text(
                          'Mark all as read',
                          style: TextStyle(
                            color: Colors.white.withOpacity(0.7),
                            fontSize: 14,
                          ),
                        ),
                      ),
                    ),
                  Expanded(
                    child: ListView.builder(
                      itemCount: notifications.length,
                      itemBuilder: (context, index) {
                        final notification = notifications[index];
                        return NotificationItem(
                          notification: notification,
                          onDelete: () =>
                              provider.deleteNotification(notification.id),
                          onMarkAsRead: provider.markAsRead,
                        );
                      },
                    ),
                  ),
                ],
              ),
      ),
    );
  }
}
