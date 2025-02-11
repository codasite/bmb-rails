import 'dart:async';
import 'package:app_links/app_links.dart';
import 'package:flutter/foundation.dart';
import 'package:bmb_mobile/core/utils/app_logger.dart';

class AppLinkProvider extends ChangeNotifier {
  final AppLinks _appLinks = AppLinks();
  StreamSubscription<Uri>? _subscription;
  Uri? _currentUri;

  AppLinkProvider() {
    _initialize();
  }

  Future<void> _initialize() async {
    _subscription = _appLinks.uriLinkStream.listen((Uri? uri) {
      _currentUri = uri;
      AppLogger.debugLog('App link received: $uri');
      notifyListeners();
    }, onError: (err) {
      AppLogger.logError('App link error: $err', StackTrace.current);
    });
  }

  Uri? getUri() {
    final uri = _currentUri;
    if (uri != null) {
      _currentUri = null;
    }
    return uri;
  }

  @override
  void dispose() {
    _subscription?.cancel();
    super.dispose();
  }
}
