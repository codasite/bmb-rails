import 'package:bmb_mobile/core/utils/app_logger.dart';
import 'package:device_info_plus/device_info_plus.dart';
import 'dart:io';

class DeviceInfo {
  /// Get device information
  static Future<DeviceData> getDeviceInfo() async {
    try {
      final deviceInfo = DeviceInfoPlugin();
      DeviceData deviceData;
      if (Platform.isIOS) {
        final iosInfo = await deviceInfo.iosInfo;
        deviceData = DeviceData(
          id: iosInfo.identifierForVendor ?? 'unknown',
          platform: 'ios',
          name: '${iosInfo.name} ${iosInfo.model}',
        );
      } else {
        final androidInfo = await deviceInfo.androidInfo;
        deviceData = DeviceData(
          id: androidInfo.id,
          platform: 'android',
          name: androidInfo.model,
        );
      }
      await AppLogger.logMessage('Device info: $deviceData');
      return deviceData;
    } catch (e, stackTrace) {
      await AppLogger.logError(
        e,
        stackTrace,
        extras: {'message': 'Failed to get device info'},
      );
      rethrow;
    }
  }
}

class DeviceData {
  final String id;
  final String platform;
  final String name;

  DeviceData({
    required this.id,
    required this.platform,
    required this.name,
  });

  @override
  String toString() {
    return 'DeviceData(id: $id, platform: $platform, name: $name)';
  }
}
