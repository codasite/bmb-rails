import 'package:bmb_mobile/features/notifications/data/models/bmb_notification.dart';
import 'package:bmb_mobile/features/notifications/data/clients/notification_client.dart';
import 'package:bmb_mobile/core/utils/app_logger.dart';

/// Manages notification operations and business logic
class NotificationManager {
  final NotificationClient _client;

  NotificationManager(this._client);

  /// Fetches all notifications for the current user
  Future<List<BmbNotification>> getNotifications() async {
    await AppLogger.debugLog(
      'Fetching notifications',
      extras: {'operation': 'getNotifications'},
    );

    final notifications = await _client.getNotifications();

    await AppLogger.debugLog(
      'Fetched notifications',
      extras: {
        'operation': 'getNotifications',
        'count': notifications.length,
        'unread_count': notifications.where((n) => !n.isRead).length,
      },
    );

    return notifications;
  }

  /// Marks a notification as read
  /// Returns the updated notification if successful, null otherwise
  Future<BmbNotification?> markAsRead(String notificationId) async {
    await AppLogger.debugLog(
      'Marking notification $notificationId as read',
      extras: {
        'operation': 'markAsRead',
        'notification_id': notificationId,
      },
    );

    final notification = await _client.markAsRead(notificationId);

    await AppLogger.debugLog(
      notification != null
          ? 'Marked notification as read'
          : 'Failed to mark notification as read',
      extras: {
        'operation': 'markAsRead',
        'notification_id': notificationId,
        'success': notification != null,
      },
    );

    return notification;
  }

  /// Marks all notifications as read for the current user
  /// Returns the number of notifications marked as read
  Future<int> markAllAsRead() async {
    await AppLogger.debugLog(
      'Marking all notifications as read',
      extras: {'operation': 'markAllAsRead'},
    );

    final count = await _client.markAllAsRead();

    await AppLogger.debugLog(
      'Marked notifications as read',
      extras: {
        'operation': 'markAllAsRead',
        'count': count,
      },
    );

    return count;
  }

  /// Deletes a notification
  /// Returns true if successful, false otherwise
  Future<bool> deleteNotification(String notificationId) async {
    await AppLogger.debugLog(
      'Deleting notification',
      extras: {
        'operation': 'deleteNotification',
        'notification_id': notificationId,
      },
    );

    final success = await _client.deleteNotification(notificationId);

    await AppLogger.debugLog(
      success ? 'Deleted notification' : 'Failed to delete notification',
      extras: {
        'operation': 'deleteNotification',
        'notification_id': notificationId,
        'success': success,
      },
    );

    return success;
  }

  // Add any additional business logic methods here
  // For example:
  // - Filtering notifications by type
  // - Sorting notifications
  // - Grouping notifications
  // - Handling notification preferences
  // - Managing notification badges/counts
}
