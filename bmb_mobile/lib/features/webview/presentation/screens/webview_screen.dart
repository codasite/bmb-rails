import 'package:bmb_mobile/core/theme/bmb_colors.dart';
import 'package:bmb_mobile/core/theme/bmb_font_weights.dart';
import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';
import 'package:bmb_mobile/core/widgets/upper_case_text.dart';
import 'package:bmb_mobile/features/webview/data/models/navigation_item.dart';
import 'package:bmb_mobile/features/webview/data/models/drawer_item.dart';
import 'package:provider/provider.dart';
import 'package:bmb_mobile/features/wp_auth/presentation/providers/auth_provider.dart';
import 'package:bmb_mobile/features/notifications/presentation/providers/fcm_token_manager_provider.dart';
import 'package:bmb_mobile/features/webview/config/bottom_nav_items.dart';
import 'package:flutter/services.dart' show rootBundle;
import 'package:bmb_mobile/features/wp_http/wp_urls.dart';
import 'package:bmb_mobile/features/webview/presentation/widgets/bmb_drawer.dart';
import 'package:bmb_mobile/features/webview/presentation/widgets/bmb_bottom_nav_bar.dart';
import 'package:bmb_mobile/features/notifications/presentation/screens/notification_screen.dart';
import 'package:bmb_mobile/features/notifications/presentation/providers/notification_provider.dart';
import 'package:bmb_mobile/features/webview/presentation/delegates/webview_navigation_delegate.dart';
import 'package:bmb_mobile/features/webview/presentation/controllers/javascript_channel_controller.dart';
import 'dart:async' show scheduleMicrotask;
import 'package:bmb_mobile/features/notifications/presentation/widgets/fcm_notification_listener.dart';

class WebViewScreen extends StatefulWidget {
  const WebViewScreen({super.key});

  @override
  State<WebViewScreen> createState() => _WebViewScreenState();
}

class _WebViewScreenState extends State<WebViewScreen> {
  static const double _refreshThreshold = 65.0;

  late final WebViewController _controller;
  late final JavaScriptChannelController _jsController;
  bool _isLoading = true;
  bool _canGoBack = false;
  int? _selectedIndex;
  String _currentTitle = 'Back My Bracket';
  double _refreshProgress = 0.0;
  bool _isLoggingOut = false;

  final List<NavigationItem> _pages = bottomNavItems;

  void _onItemTapped(int index) {
    setState(() {
      _selectedIndex = index;
    });
    _loadUrl(_pages[index].path);
  }

  void _onDrawerItemTap(DrawerItem item) async {
    if (item.path == '/wp-login.php?action=logout') {
      setState(() {
        _isLoggingOut = true;
      });

      try {
        await context.read<FCMTokenManagerProvider>().deregisterToken();
        if (mounted) {
          await context.read<AuthProvider>().logout();
        }
        if (mounted) {
          Navigator.pushReplacementNamed(context, '/login');
        }
      } finally {
        setState(() {
          _isLoggingOut = false;
        });
      }
    } else {
      await _loadUrl(item.path);
      if (mounted) {
        Navigator.pop(context);
      }
    }
  }

  Future<void> _loadUrl(String path, {bool prependBaseUrl = true}) async {
    await WidgetsBinding.instance.endOfFrame;
    if (!mounted) return;
    final url = prependBaseUrl ? WpUrls.baseUrl + path : path;
    await _controller.loadRequest(Uri.parse(url));
  }

  Future<void> _injectPullToRefreshJS() async {
    final String js =
        await rootBundle.loadString('assets/js/pull_to_refresh.js');
    final String configuredJs =
        js.replaceAll('REFRESH_THRESHOLD', _refreshThreshold.toString());
    await WidgetsBinding.instance.endOfFrame;
    if (!mounted) return;
    await _controller.runJavaScript(configuredJs);
  }

  void _handleNotificationNavigation() async {
    final result = await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => const NotificationScreen(),
      ),
    );

    if (mounted && result != null && result is String) {
      await WidgetsBinding.instance.endOfFrame;
      if (!mounted) return;
      await _loadUrl(result, prependBaseUrl: false);
    }
  }

  void _handleLoadingChanged(bool isLoading) {
    setState(() {
      _isLoading = isLoading;
    });
    _updateCanGoBack();
  }

  void _handlePageFinished(String url) async {
    await _injectPullToRefreshJS();
    if (!mounted) return;
    _setAppBarTitle();
    _updateCanGoBack();
  }

  Future<void> _updateCanGoBack() async {
    if (!mounted) return;
    final value = await _controller.canGoBack();
    if (mounted) {
      setState(() {
        _canGoBack = value;
      });
    }
  }

  void _setAppBarTitle() {
    scheduleMicrotask(() async {
      if (!mounted) return;
      final title = await _controller.getTitle();
      if (!mounted) return;
      String? trimmedTitle = title?.replaceAll(RegExp(r' - BackMyBracket'), '');
      setState(() {
        _currentTitle = trimmedTitle ?? 'Loading';
      });
    });
  }

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _initWebView();
    });
  }

  // @override
  // void didChangeDependencies() {
  //   super.didChangeDependencies();
  //   final args = ModalRoute.of(context)?.settings.arguments;
  //   if (args != null && args is String) {
  //     WidgetsBinding.instance.addPostFrameCallback((_) {
  //       if (mounted) {
  //         _loadUrl(args, prependBaseUrl: false);
  //       }
  //     });
  //   }
  // }

  @override
  void dispose() {
    _jsController.removeAllChannels();
    super.dispose();
  }

  Future<void> _initWebView() async {
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setUserAgent('BackMyBracket-MobileApp');

    _jsController = JavaScriptChannelController(_controller);

    await _jsController.addChannel(
      'Flutter',
      (message) {
        if (message.message == 'refresh') {
          scheduleMicrotask(() {
            if (mounted) {
              _controller.reload();
              setState(() => _refreshProgress = 0.0);
            }
          });
        } else if (message.message.startsWith('pull:')) {
          final pullAmount = double.parse(message.message.split(':')[1]);
          setState(() => _refreshProgress =
              (pullAmount / _refreshThreshold).clamp(0.0, 1.0));
        }
      },
    );

    _controller.setNavigationDelegate(
      WebViewNavigationDelegate(
        onLoadingChanged: _handleLoadingChanged,
        onPageCompleted: _handlePageFinished,
        onLogout: () {
          if (!mounted) return;
          context.read<AuthProvider>().logout();
          Navigator.pushReplacementNamed(context, '/login');
        },
      ),
    );

    await _loadUrl('/dashboard/tournaments/');
  }

  Future<bool> _handleBackPress() async {
    await WidgetsBinding.instance.endOfFrame;
    if (!mounted) return true;
    if (await _controller.canGoBack()) {
      await _controller.goBack();
      return false;
    }
    return true;
  }

  @override
  Widget build(BuildContext context) {
    return PopScope<Object?>(
      canPop: !_canGoBack,
      onPopInvokedWithResult: (bool didPop, Object? result) async {
        if (didPop) {
          return;
        }
        final shouldPop = await _handleBackPress();
        if (context.mounted && shouldPop) {
          Navigator.pop(context);
        }
      },
      child: FCMNotificationListener(
        onLoadUrl: _loadUrl,
        child: Stack(
          children: [
            Scaffold(
              backgroundColor: BmbColors.ddBlue,
              appBar: AppBar(
                backgroundColor: BmbColors.darkBlue,
                iconTheme: const IconThemeData(color: Colors.white),
                leading: Builder(
                  builder: (BuildContext context) {
                    return IconButton(
                      icon: const Icon(Icons.menu),
                      onPressed: () {
                        Scaffold.of(context).openDrawer();
                      },
                    );
                  },
                ),
                title: Center(
                  child: UpperCaseText(
                    _currentTitle,
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 16,
                      fontVariations: BmbFontWeights.w500,
                    ),
                  ),
                ),
                actions: [
                  if (_canGoBack)
                    IconButton(
                      icon: const Icon(Icons.arrow_back),
                      onPressed: () {
                        _controller.goBack();
                      },
                      color: Colors.white,
                    ),
                  Padding(
                    padding: const EdgeInsets.only(right: 8),
                    child: IconButton(
                      icon: Badge(
                        isLabelVisible:
                            context.watch<NotificationProvider>().unreadCount >
                                0,
                        backgroundColor: Colors.red,
                        smallSize: 16,
                        label: Text(
                          context.watch<NotificationProvider>().unreadCount > 99
                              ? '99+'
                              : '${context.watch<NotificationProvider>().unreadCount}',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 10,
                          ),
                        ),
                        child: const Icon(
                          Icons.notifications_outlined,
                          color: Colors.white,
                        ),
                      ),
                      onPressed: _handleNotificationNavigation,
                    ),
                  ),
                ],
              ),
              drawer: BmbDrawer(
                onDrawerItemTap: _onDrawerItemTap,
              ),
              body: SafeArea(
                child: Container(
                  color: BmbColors.ddBlue,
                  child: Stack(
                    children: [
                      WebViewWidget(controller: _controller),
                      if (_isLoading)
                        Container(
                          color: Colors.transparent.withOpacity(0.5),
                          child: const Center(
                            child: CircularProgressIndicator(
                              color: BmbColors.blue,
                            ),
                          ),
                        ),
                      if (_refreshProgress > 0)
                        const Positioned(
                          top: 8,
                          left: 0,
                          right: 0,
                          child: Center(
                            child: Icon(
                              Icons.refresh,
                              color: BmbColors.blue,
                              size: 24,
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
              ),
              bottomNavigationBar: BmbBottomNavBar(
                pages: _pages,
                selectedIndex: _selectedIndex ?? 0,
                onItemTapped: _onItemTapped,
              ),
            ),
            if (_isLoggingOut)
              Container(
                color: Colors.black54,
                child: const Center(
                  child: CircularProgressIndicator(
                    color: BmbColors.blue,
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
