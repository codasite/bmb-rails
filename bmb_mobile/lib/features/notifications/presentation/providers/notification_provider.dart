import 'package:flutter/foundation.dart';
import 'package:bmb_mobile/features/notifications/data/models/bmb_notification.dart';
import 'package:bmb_mobile/features/notifications/domain/services/notification_manager.dart';

/// Manages the client-side state for notifications
class NotificationProvider extends ChangeNotifier {
  final NotificationManager _manager;
  List<BmbNotification> _notifications = [];
  bool _isLoading = false;

  NotificationProvider({
    required NotificationManager manager,
  }) : _manager = manager;

  List<BmbNotification> get notifications => _notifications;
  bool get isLoading => _isLoading;
  int get unreadCount => _notifications.where((n) => !n.isRead).length;

  Future<void> fetchNotifications() async {
    _isLoading = true;
    notifyListeners();

    _notifications = await _manager.getNotifications();

    _isLoading = false;
    notifyListeners();
  }

  Future<void> markAsRead(int notificationId) async {
    final updatedNotification = await _manager.markAsRead(notificationId);
    if (updatedNotification != null) {
      final index = _notifications.indexWhere((n) => n.id == notificationId);
      if (index != -1) {
        _notifications[index] = updatedNotification;
        notifyListeners();
      }
    }
  }

  Future<void> markAllAsRead() async {
    // Store original notifications in case we need to revert
    final originalNotifications = List<BmbNotification>.from(_notifications);

    // Optimistically update all notifications
    _notifications = _notifications
        .map((notification) => BmbNotification(
              id: notification.id,
              title: notification.title,
              message: notification.message,
              timestamp: notification.timestamp,
              isRead: true,
              link: notification.link,
            ))
        .toList();
    notifyListeners();

    try {
      await _manager.markAllAsRead();
    } catch (e) {
      // Revert on failure
      _notifications = originalNotifications;
      notifyListeners();
      rethrow;
    }
  }

  Future<void> deleteNotification(int id) async {
    // Optimistically remove the notification from the list
    final index = _notifications.indexWhere((n) => n.id == id);
    if (index == -1) return;

    final notification = _notifications[index];
    _notifications.removeAt(index);
    notifyListeners();

    try {
      await _manager.deleteNotification(id);
    } catch (e) {
      // If the deletion fails, add the notification back
      _notifications.insert(index, notification);
      notifyListeners();
      rethrow;
    }
  }
}
