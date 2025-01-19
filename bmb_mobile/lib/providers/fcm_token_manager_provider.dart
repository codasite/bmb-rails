import 'package:flutter/foundation.dart';
import 'package:bmb_mobile/firebase/fcm_token_manager.dart';

class FCMTokenManagerProvider with ChangeNotifier {
  final FCMTokenManager _fcmManager;

  FCMTokenManagerProvider({
    required FCMTokenManager fcmManager,
  }) : _fcmManager = fcmManager;

  FCMTokenManager get service => _fcmManager;

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
