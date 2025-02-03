import 'dart:convert';
import 'package:bmb_mobile/features/notifications/data/models/bmb_notification.dart';
import 'package:bmb_mobile/features/wp_http/domain/service/wp_http_client.dart';
import 'package:bmb_mobile/features/wp_http/wp_urls.dart';
import 'package:bmb_mobile/core/utils/app_logger.dart';

/// Handles all HTTP interactions with the notification API endpoints
class NotificationClient {
  final WpHttpClient _httpClient;

  NotificationClient(this._httpClient);

  /// Fetches all notifications for the current user
  Future<List<BmbNotification>> getNotifications() async {
    try {
      final response = await _httpClient.get(WpUrls.notificationsPath);
      final List<dynamic> jsonList = jsonDecode(response!.body);
      return jsonList.map((json) => BmbNotification.fromJson(json)).toList();
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Failed to fetch notifications'},
      );
      return [];
    }
  }

  /// Marks a single notification as read
  Future<BmbNotification?> markAsRead(String notificationId) async {
    try {
      final response = await _httpClient.put(
        WpUrls.notificationReadPath(notificationId),
      );
      if (response == null) {
        return null;
      }

      final json = jsonDecode(response.body);
      return BmbNotification.fromJson(json);
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {
          'message': 'Failed to mark notification as read',
          'notification_id': notificationId,
        },
      );
      return null;
    }
  }

  /// Marks all notifications as read for the current user
  Future<int> markAllAsRead() async {
    try {
      final response = await _httpClient.put(WpUrls.notificationsReadAllPath);
      if (response == null) {
        return 0;
      }

      final json = jsonDecode(response.body);
      return json['marked_as_read'] as int;
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Failed to mark all notifications as read'},
      );
      return 0;
    }
  }

  /// Deletes a notification
  Future<bool> deleteNotification(String notificationId) async {
    try {
      final response = await _httpClient.delete(
        WpUrls.notificationPath(notificationId),
      );
      if (response == null) {
        return false;
      }

      final json = jsonDecode(response.body);
      return json['deleted'] as bool;
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {
          'message': 'Failed to delete notification',
          'notification_id': notificationId,
        },
      );
      return false;
    }
  }
}
