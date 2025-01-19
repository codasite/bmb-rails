import 'package:flutter/foundation.dart';
import 'package:bmb_mobile/firebase/fcm_token_manager.dart';
import 'package:bmb_mobile/providers/http_client_provider.dart';

class FCMTokenServiceProvider with ChangeNotifier {
  late final FcmTokenManager _fcmManager;

  FCMTokenServiceProvider(HttpClientProvider httpProvider) {
    _fcmManager = FcmTokenManager(httpProvider.sessionClient);
  }

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
