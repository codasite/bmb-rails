import 'package:flutter/foundation.dart';
import 'package:bmb_mobile/features/notifications/domain/services/fcm_token_manager.dart';

class FCMTokenManagerProvider with ChangeNotifier {
  final FcmTokenManager _fcmManager;

  FCMTokenManagerProvider({
    required FcmTokenManager fcmManager,
  }) : _fcmManager = fcmManager;

  FcmTokenManager get service => _fcmManager;

  Future<void> initialize() async {
    await _fcmManager.initialize();
    await _fcmManager.setupToken();
  }

  Future<void> deregisterToken() async {
    await _fcmManager.deregisterToken();
  }

  Future<void> updateStatus() async {
    await _fcmManager.updateStatus();
  }
}
