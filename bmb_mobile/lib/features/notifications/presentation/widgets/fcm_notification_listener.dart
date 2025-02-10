import 'package:bmb_mobile/core/utils/app_logger.dart';
import 'package:bmb_mobile/features/notifications/data/models/bmb_notification.dart';
import 'package:bmb_mobile/features/notifications/presentation/screens/notification_screen.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:bmb_mobile/features/notifications/presentation/providers/notification_provider.dart';
import 'package:bmb_mobile/features/notifications/presentation/widgets/notification_banner.dart';

/// Process messages when the app is in the background

/// FCM Message handlers. See https://firebase.google.com/docs/cloud-messaging/flutter/receive and https://firebase.flutter.dev/docs/messaging/notifications/#handling-interaction
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
    _setupMessageListeners();
  }

  BmbNotification? _parseRemoteMessage(RemoteMessage message) {
    try {
      return BmbNotification.fromRemoteMessage(message);
    } catch (e, stackTrace) {
      AppLogger.logError(e, stackTrace,
          extras: {'message': 'Error parsing remote message'},
          printStackTrace: true);
      return null;
    }
  }

  void _handleNotificationTap(BmbNotification notification) async {
    if (notification.id != null && notification.link != null) {
      context.read<NotificationProvider>().markAsRead(notification.id!);
      await widget.onLoadUrl(notification.link!, prependBaseUrl: false);
    } else if (notification.link != null) {
      await widget.onLoadUrl(notification.link!, prependBaseUrl: false);
    } else {
      Navigator.pushAndRemoveUntil(
        context,
        MaterialPageRoute(
          builder: (context) => NotificationScreen(
            onLoadUrl: widget.onLoadUrl,
          ),
        ),
        ModalRoute.withName('/app'),
      );
    }
  }

  void _setupMessageListeners() async {
    _handleInitialMessage();
    FirebaseMessaging.onMessage.listen(_handleForegroundMessageReceived);
    FirebaseMessaging.onMessageOpenedApp.listen(_handleBackgroundMessageOpened);
  }

  BmbNotification? _handleMessageReceived(RemoteMessage message) {
    _debugLogMessage(message);
    if (!mounted) return null;
    context.read<NotificationProvider>().fetchNotifications();
    return _parseRemoteMessage(message);
  }

  /// Handle notifications when notification tap causes the app to be opened from a terminated state
  void _handleInitialMessage() async {
    RemoteMessage? initialMessage =
        await FirebaseMessaging.instance.getInitialMessage();
    if (initialMessage != null) {
      final notification = _handleMessageReceived(initialMessage);
      if (mounted && notification != null) {
        _handleNotificationTap(notification);
      }
    }
  }

  /// Handle notifications when the app is in the background and the notification is tapped
  void _handleBackgroundMessageOpened(RemoteMessage message) {
    final notification = _handleMessageReceived(message);
    if (mounted && notification != null) {
      _handleNotificationTap(notification);
    }
  }

  /// Handle notifications when the app is in the foreground
  void _handleForegroundMessageReceived(RemoteMessage message) {
    final notification = _handleMessageReceived(message);
    if (mounted && notification != null) {
      final banner = NotificationBanner(
        notification: notification,
        onDismiss: () {
          ScaffoldMessenger.of(context).hideCurrentMaterialBanner();
        },
        onView: () => _handleNotificationTap(notification),
      );

      ScaffoldMessenger.of(context).showMaterialBanner(
        banner.build(context) as MaterialBanner,
      );
    }
  }

  void _debugLogMessage(RemoteMessage message) {
    AppLogger.debugLog('Remote message received in foreground:');
    AppLogger.debugLog('Title: ${message.notification?.title}');
    AppLogger.debugLog('Body: ${message.notification?.body}');
    AppLogger.debugLog('Data: ${message.data}');
  }

  @override
  Widget build(BuildContext context) {
    return widget.child;
  }
}
