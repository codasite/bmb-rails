import 'package:bmb_mobile/core/utils/app_logger.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:bmb_mobile/features/notifications/presentation/providers/notification_provider.dart';
import 'package:bmb_mobile/features/notifications/presentation/screens/notification_screen.dart';
import 'package:bmb_mobile/features/webview/presentation/providers/webview_provider.dart';

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

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(message.notification?.title ?? 'New notification'),
          action: SnackBarAction(
            label: 'View',
            onPressed: () async {
              final result = await Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => const NotificationScreen(),
                ),
              );

              if (result != null && result is String && mounted) {
                context
                    .read<WebViewProvider>()
                    .loadUrl(result, prependBaseUrl: false);
              }
            },
          ),
          behavior: SnackBarBehavior.floating,
          duration: const Duration(seconds: 4),
        ),
      );

      AppLogger.debugLog('remote push message received');
    });
  }

  @override
  Widget build(BuildContext context) {
    return widget.child;
  }
}
