import 'dart:convert';

class WpAppPassword {
  final String username;
  final String password;
  final String uuid;

  WpAppPassword({
    required this.username,
    required this.password,
    required this.uuid,
  });

  // Create from JSON (e.g., from SharedPreferences)
  factory WpAppPassword.fromJson(Map<String, dynamic> json) {
    return WpAppPassword(
      username: json['username'] as String,
      password: json['password'] as String,
      uuid: json['uuid'] as String,
    );
  }

  // Convert to JSON for storage
  Map<String, dynamic> toJson() => {
        'username': username,
        'password': password,
        'uuid': uuid,
      };

  // Create Basic Auth header
  String get basicAuth =>
      'Basic ${base64Encode(utf8.encode('$username:$password'))}';
}
