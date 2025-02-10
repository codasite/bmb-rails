import 'package:flutter_app_badge_control/flutter_app_badge_control.dart';
import 'package:shared_preferences/shared_preferences.dart';

class AppBadgeManager {
  static const String _badgeCountKey = 'app_badge_count';

  static Future<void> updateBadgeCount(int count) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt(_badgeCountKey, count);
    await FlutterAppBadgeControl.updateBadgeCount(count);
  }

  static Future<void> incrementBadgeCount() async {
    final prefs = await SharedPreferences.getInstance();
    final currentCount = prefs.getInt(_badgeCountKey) ?? 0;
    final newCount = currentCount + 1;
    await prefs.setInt(_badgeCountKey, newCount);
    await FlutterAppBadgeControl.updateBadgeCount(newCount);
  }
}
