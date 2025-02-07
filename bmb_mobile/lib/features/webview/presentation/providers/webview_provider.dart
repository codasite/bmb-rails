import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';
import 'package:bmb_mobile/features/wp_http/wp_urls.dart';

class WebViewProvider extends ChangeNotifier {
  late final WebViewController controller;
  bool _isLoading = true;
  bool _canGoBack = false;

  WebViewProvider() {
    _initController();
  }

  bool get isLoading => _isLoading;
  bool get canGoBack => _canGoBack;

  void _initController() {
    controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setUserAgent('BackMyBracket-MobileApp');
  }

  void loadUrl(String path, {bool prependBaseUrl = true}) {
    final url = prependBaseUrl ? WpUrls.baseUrl + path : path;
    controller.loadRequest(Uri.parse(url));
  }

  Future<bool> goBack() async {
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
