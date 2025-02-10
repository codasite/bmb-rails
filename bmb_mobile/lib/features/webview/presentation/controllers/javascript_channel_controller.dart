import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';

class JavaScriptChannelController {
  final WebViewController controller;
  final Set<String> _registeredChannels = {};

  JavaScriptChannelController(this.controller);

  Future<void> addChannel(
    String name,
    void Function(JavaScriptMessage) onMessageReceived,
  ) async {
    await WidgetsBinding.instance.endOfFrame;
    if (_registeredChannels.contains(name)) {
      await controller.removeJavaScriptChannel(name);
      _registeredChannels.remove(name);
    }
    _registeredChannels.add(name);
    controller.addJavaScriptChannel(
      name,
      onMessageReceived: onMessageReceived,
    );
  }

  Future<void> removeAllChannels() async {
    await WidgetsBinding.instance.endOfFrame;
    for (final channel in _registeredChannels.toList()) {
      await controller.removeJavaScriptChannel(channel);
    }
    _registeredChannels.clear();
  }
}
