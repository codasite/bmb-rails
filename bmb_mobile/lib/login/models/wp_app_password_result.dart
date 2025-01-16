import 'package:bmb_mobile/login/models/wp_app_password.dart';

class WpAppPasswordResult {
  final bool success;
  final WpAppPassword? password;
  final int statusCode;

  WpAppPasswordResult({
    required this.success,
    this.password,
    required this.statusCode,
  });

  // Helper constructor for successful results
  factory WpAppPasswordResult.success(WpAppPassword password, int statusCode) {
    return WpAppPasswordResult(
      success: true,
      password: password,
      statusCode: statusCode,
    );
  }

  // Helper constructor for failures
  factory WpAppPasswordResult.failure(int statusCode) {
    return WpAppPasswordResult(
      success: false,
      password: null,
      statusCode: statusCode,
    );
  }

  // Helper constructor for client errors
  factory WpAppPasswordResult.error() {
    return WpAppPasswordResult(
      success: false,
      password: null,
      statusCode: 0,
    );
  }
}
