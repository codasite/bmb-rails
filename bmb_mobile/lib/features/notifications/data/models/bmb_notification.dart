import 'package:firebase_messaging/firebase_messaging.dart';

class BmbNotification {
  final int id;
  final String title;
  final String? message;
  final DateTime timestamp;
  final bool isRead;
  final String? link;

  BmbNotification({
    required this.id,
    required this.title,
    required this.timestamp,
    this.message,
    this.isRead = false,
    this.link,
  });

  factory BmbNotification.fromJson(Map<String, dynamic> json) {
    return BmbNotification(
      id: json['id'],
      title: json['title'],
      message: json['message'],
      timestamp: DateTime.parse(json['timestamp']),
      isRead: json['is_read'] ?? false,
      link: json['link'],
    );
  }

  factory BmbNotification.fromRemoteMessage(RemoteMessage message) {
    return BmbNotification(
      id: int.parse(message.data['id']),
      title: message.notification?.title ?? '',
      message: message.notification?.body ?? '',
      timestamp: DateTime.parse(message.data['timestamp']),
      link: message.data['link'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'message': message,
      'timestamp': timestamp.toIso8601String(),
      'is_read': isRead,
      'link': link,
    };
  }

  @override
  String toString() {
    return 'BmbNotification(id: $id, title: $title, message: $message, timestamp: $timestamp, isRead: $isRead, link: $link)';
  }
}
