import 'package:bmb_mobile/core/utils/app_logger.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:bmb_mobile/features/notifications/presentation/providers/notification_provider.dart';
import 'package:bmb_mobile/features/notifications/presentation/widgets/notification_banner.dart';

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
      if (!mounted) return;
      context.read<NotificationProvider>().fetchNotifications();

      final banner = NotificationBanner(
        message: message,
        onDismiss: () {
          ScaffoldMessenger.of(context).hideCurrentMaterialBanner();
        },
      );

      ScaffoldMessenger.of(context).showMaterialBanner(
        banner.build(context) as MaterialBanner,
      );

      AppLogger.debugLog('remote push message received');
    });
  }

  @override
  Widget build(BuildContext context) {
    return widget.child;
  }
}
