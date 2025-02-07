import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';
import 'package:bmb_mobile/features/wp_http/wp_urls.dart';

class WebViewProvider extends ChangeNotifier {
  late final WebViewController controller;
  bool _isLoading = true;
  bool _canGoBack = false;
  final Set<String> _registeredChannels = {};

  WebViewProvider() {
    _initController();
  }

  bool get isLoading => _isLoading;
  bool get canGoBack => _canGoBack;

  void _initController() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      controller = WebViewController()
        ..setJavaScriptMode(JavaScriptMode.unrestricted)
        ..setUserAgent('BackMyBracket-MobileApp');
    });
  }

  Future<void> addJavaScriptChannel(
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

  Future<void> removeAllJavaScriptChannels() async {
    await WidgetsBinding.instance.endOfFrame;
    for (final channel in _registeredChannels.toList()) {
      await controller.removeJavaScriptChannel(channel);
    }
    _registeredChannels.clear();
  }

  @override
  void dispose() {
    removeAllJavaScriptChannels();
    super.dispose();
  }

  Future<void> loadUrl(String path, {bool prependBaseUrl = true}) async {
    await WidgetsBinding.instance.endOfFrame;
    final url = prependBaseUrl ? WpUrls.baseUrl + path : path;
    controller.loadRequest(Uri.parse(url));
  }

  Future<bool> goBack() async {
    await WidgetsBinding.instance.endOfFrame;
    if (await controller.canGoBack()) {
      await controller.goBack();
      return true;
    }
    return false;
  }

  void setLoading(bool value) {
    _isLoading = value;
    notifyListeners();
  }

  void setCanGoBack(bool value) {
    _canGoBack = value;
    notifyListeners();
  }
}
