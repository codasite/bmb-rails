import 'package:bmb_mobile/core/theme/bmb_colors.dart';
import 'package:bmb_mobile/features/notifications/data/models/bmb_notification.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:bmb_mobile/core/widgets/upper_case_text.dart';

class NotificationScreen extends StatefulWidget {
  const NotificationScreen({super.key});

  @override
  State<NotificationScreen> createState() => _NotificationScreenState();
}

class _NotificationScreenState extends State<NotificationScreen> {
  // Test data - replace with real data later
  final List<BmbNotification> notifications = [
    BmbNotification(
      id: '1',
      title: 'New Tournament Available',
      message: 'March Madness 2024 brackets are now open for predictions!',
      timestamp: DateTime.now().subtract(const Duration(hours: 2)),
      link: '/dashboard/tournaments',
    ),
    BmbNotification(
      id: '2',
      title: 'Bracket Update',
      message: 'Your bracket "Championship Dreams" has been updated.',
      timestamp: DateTime.now().subtract(const Duration(days: 1)),
      isRead: true,
      link: '/dashboard/brackets',
    ),
    BmbNotification(
      id: '3',
      title: 'Tournament Starting Soon',
      message: 'The tournament "Spring Classic" starts in 24 hours.',
      timestamp: DateTime.now().subtract(const Duration(days: 2)),
      link: '/dashboard/tournaments',
    ),
  ];

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
      body: notifications.isEmpty
          ? const Center(
              child: Text(
                'No notifications',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 16,
                ),
              ),
            )
          : ListView.builder(
              itemCount: notifications.length,
              itemBuilder: (context, index) {
                final notification = notifications[index];
                return Card(
                  margin: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 8,
                  ),
                  color: notification.isRead
                      ? BmbColors.darkBlue
                      : BmbColors.blue.withOpacity(0.9),
                  child: InkWell(
                    onTap: () {
                      // Handle notification tap
                      if (notification.link != null) {
                        // Navigate to the link
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
                );
              },
            ),
    );
  }
}
