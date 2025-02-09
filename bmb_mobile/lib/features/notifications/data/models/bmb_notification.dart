class BmbNotification {
  final int id;
  final String title;
  final String message;
  final DateTime timestamp;
  final bool isRead;
  final String? link;

  BmbNotification({
    required this.id,
    required this.title,
    required this.message,
    required this.timestamp,
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
