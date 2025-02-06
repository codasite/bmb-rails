import 'package:bmb_mobile/core/utils/app_logger.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:bmb_mobile/features/notifications/presentation/providers/notification_provider.dart';

class FCMNotificationListener extends StatefulWidget {
  final Widget child;

  const FCMNotificationListener({
    super.key,
    required this.child,
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
      // Refresh notifications when a new message is received
      if (!mounted) return;
      AppLogger.debugLog('remote push message recieved');
      context.read<NotificationProvider>().fetchNotifications();
    });
  }

  @override
  Widget build(BuildContext context) {
    return widget.child;
  }
}
