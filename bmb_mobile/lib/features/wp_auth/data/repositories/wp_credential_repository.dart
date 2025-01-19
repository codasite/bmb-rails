import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:bmb_mobile/features/wp_auth/data/models/wp_app_password.dart';

class WpCredentialRepository {
  static const String _appPasswordStorageKey = 'wp_app_password';

  Future<void> storeCredentials(WpAppPassword appPassword) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(
        _appPasswordStorageKey, jsonEncode(appPassword.toJson()));
  }

  Future<WpAppPassword?> getStoredCredentials() async {
    final prefs = await SharedPreferences.getInstance();
    final credentialsString = prefs.getString(_appPasswordStorageKey);
    if (credentialsString != null) {
      final json = jsonDecode(credentialsString) as Map<String, dynamic>;
      return WpAppPassword.fromJson(json);
    }
    return null;
  }

  Future<void> clearCredentials() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_appPasswordStorageKey);
  }
}
