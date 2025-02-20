import 'package:flutter/foundation.dart';
import 'package:bmb_mobile/features/wp_auth/domain/services/wp_auth.dart';

class AuthProvider with ChangeNotifier {
  final WpAuth _auth;

  List<String> getErrorsList() {
    return _auth.getErrorList();
  }

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

  Future<bool> register(String email, String username) async {
    final success = await _auth.register(email, username);
    notifyListeners();
    return success;
  }

  Future<bool> requestPasswordReset(String email) async {
    final success = await _auth.requestPasswordReset(email);
    return success;
  }

  Future<void> logout() async {
    await _auth.logout();
    notifyListeners();
  }
}
