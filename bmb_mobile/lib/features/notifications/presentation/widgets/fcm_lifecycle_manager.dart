import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:bmb_mobile/features/notifications/presentation/providers/fcm_token_manager_provider.dart';
import 'package:bmb_mobile/core/utils/app_logger.dart';
import 'dart:async';

class FCMLifecycleManager extends StatefulWidget {
  final Widget child;

  const FCMLifecycleManager({
    super.key,
    required this.child,
  });

  @override
  State<FCMLifecycleManager> createState() => _FCMLifecycleManagerState();
}

class _FCMLifecycleManagerState extends State<FCMLifecycleManager> {
  late final AppLifecycleListener _lifecycleListener;
  Timer? _statusUpdateTimer;

  @override
  void initState() {
    super.initState();

    // Initialize FCM
    context.read<FCMTokenManagerProvider>().initialize();

    // Setup lifecycle listener
    _lifecycleListener = AppLifecycleListener(
      onStateChange: _handleLifecycleStateChange,
    );

    _startStatusUpdates();
  }

  void _handleLifecycleStateChange(AppLifecycleState state) async {
    await AppLogger.debugLog('App lifecycle state changed: $state');

    if (state == AppLifecycleState.resumed) {
      await AppLogger.debugLog('App resumed, restarting FCM status updates');
      if (mounted) {
        await context.read<FCMTokenManagerProvider>().updateStatus();
        _startStatusUpdates();
      }
    }
  }

  void _startStatusUpdates() {
    AppLogger.debugLog('Starting FCM status updates');
    _statusUpdateTimer?.cancel();
    _statusUpdateTimer = Timer.periodic(const Duration(hours: 24), (_) async {
      if (mounted) {
        AppLogger.debugLog('Updating FCM status from timer');
        await context.read<FCMTokenManagerProvider>().updateStatus();
      }
    });
  }

  @override
  void dispose() {
    AppLogger.debugLog('Disposing of FCM lifecycle manager');
    _statusUpdateTimer?.cancel();
    _lifecycleListener.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => widget.child;
}
