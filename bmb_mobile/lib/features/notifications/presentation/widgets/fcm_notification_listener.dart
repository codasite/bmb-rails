import 'package:bmb_mobile/core/utils/app_logger.dart';
import 'package:bmb_mobile/features/notifications/data/models/bmb_notification.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:bmb_mobile/features/notifications/presentation/providers/notification_provider.dart';
import 'package:bmb_mobile/features/notifications/presentation/widgets/notification_banner.dart';

class FCMNotificationListener extends StatefulWidget {
  final Widget child;
  final Future<void> Function(String, {bool prependBaseUrl}) onLoadUrl;

  const FCMNotificationListener({
    super.key,
    required this.child,
    required this.onLoadUrl,
  });

  @override
  State<FCMNotificationListener> createState() =>
      _FCMNotificationListenerState();
}

class _FCMNotificationListenerState extends State<FCMNotificationListener> {
  @override
  void initState() {
    super.initState();
    _setupMessageListener();
  }

  void _setupMessageListener() {
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      if (!mounted) return;
      context.read<NotificationProvider>().fetchNotifications();

      AppLogger.debugLog('Remote message received:');
      AppLogger.debugLog('Title: ${message.notification?.title}');
      AppLogger.debugLog('Body: ${message.notification?.body}');
      AppLogger.debugLog('Data: ${message.data}');
      AppLogger.debugLog('MessageId: ${message.messageId}');
      AppLogger.debugLog('SenderId: ${message.senderId}');
      AppLogger.debugLog('SentTime: ${message.sentTime}');
      AppLogger.debugLog('ThreadId: ${message.threadId}');
      AppLogger.debugLog('TTL: ${message.ttl}');

      try {
        final notification = BmbNotification.fromRemoteMessage(message);

        final banner = NotificationBanner(
          notification: notification,
          onDismiss: () {
            ScaffoldMessenger.of(context).hideCurrentMaterialBanner();
          },
          onLoadUrl: widget.onLoadUrl,
        );

        ScaffoldMessenger.of(context).showMaterialBanner(
          banner.build(context) as MaterialBanner,
        );

        AppLogger.debugLog('remote push message received');
      } catch (e) {
        AppLogger.logError(e, StackTrace.current,
            extras: {'message': 'Error parsing remote message'});
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return widget.child;
  }
}
