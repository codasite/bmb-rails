import 'package:flutter/foundation.dart';
import 'package:bmb_mobile/auth/wp_auth.dart';

class AuthProvider with ChangeNotifier {
  final WpAuth _auth;

  AuthProvider({
    required WpAuth auth,
  }) : _auth = auth;

  bool get isAuthenticated => _auth.isAuthenticated;

  Future<void> refreshAuthStatus() async {
    await _auth.refreshAuthStatus();
    notifyListeners();
  }

  Future<bool> login(String username, String password) async {
    final success = await _auth.login(username, password);
    notifyListeners();
    return success;
  }

  Future<void> logout() async {
    await _auth.logout();
    notifyListeners();
  }
}
