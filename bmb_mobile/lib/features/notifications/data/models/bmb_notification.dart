import 'package:firebase_messaging/firebase_messaging.dart';

class BmbNotification {
  final int? id;
  final String title;
  final String message;
  final DateTime? timestamp;
  final bool isRead;
  final String? link;

  BmbNotification({
    this.id,
    required this.title,
    required this.message,
    this.timestamp,
    this.isRead = false,
    this.link,
  });

  factory BmbNotification.fromJson(Map<String, dynamic> json) {
    return BmbNotification(
      id: json['id'],
      title: json['title'],
      message: json['message'],
      timestamp:
          json['timestamp'] != null ? DateTime.parse(json['timestamp']) : null,
      isRead: json['is_read'],
      link: json['link'],
    );
  }

  factory BmbNotification.fromRemoteMessage(RemoteMessage message) {
    return BmbNotification(
      id: message.data['id'] != null ? int.parse(message.data['id']) : null,
      title: message.notification?.title ?? '',
      message: message.notification?.body ?? '',
      timestamp: message.data['timestamp'] != null
          ? DateTime.parse(message.data['timestamp'])
          : null,
      link: message.data['link'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'message': message,
      'timestamp': timestamp?.toIso8601String(),
      'is_read': isRead,
      'link': link,
    };
  }

  @override
  String toString() {
    return 'BmbNotification(id: $id, title: $title, message: $message, timestamp: $timestamp, isRead: $isRead, link: $link)';
  }
}
