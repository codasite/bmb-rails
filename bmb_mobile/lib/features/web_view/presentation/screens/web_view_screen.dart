import 'package:bmb_mobile/core/theme/bmb_colors.dart';
import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';
import 'package:bmb_mobile/core/widgets/upper_case_text.dart';
import 'package:bmb_mobile/features/web_view/data/models/navigation_item.dart';
import 'package:bmb_mobile/features/web_view/data/models/drawer_item.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:provider/provider.dart';
import 'package:bmb_mobile/features/wp_auth/presentation/providers/auth_provider.dart';
import 'package:bmb_mobile/features/notifications/presentation/providers/fcm_token_manager_provider.dart';
import 'package:bmb_mobile/features/web_view/config/bottom_nav_items.dart';
import 'package:flutter/services.dart' show rootBundle;
import 'package:bmb_mobile/features/wp_http/wp_urls.dart';
import 'package:bmb_mobile/features/web_view/presentation/widgets/bmb_drawer.dart';
import 'package:bmb_mobile/features/web_view/presentation/widgets/bmb_bottom_nav_bar.dart';

class WebViewScreen extends StatefulWidget {
  const WebViewScreen({super.key});

  @override
  State<WebViewScreen> createState() => _WebViewScreenState();
}

class _WebViewScreenState extends State<WebViewScreen> {
  static const double _refreshThreshold = 65.0;

  late final WebViewController controller;
  int? _selectedIndex;
  String _currentTitle = 'Back My Bracket';
  bool _isLoading = true;
  bool _canGoBack = false;
  double _refreshProgress = 0.0;
  bool _isLoggingOut = false;

  final List<NavigationItem> _pages = bottomNavItems;

  void _loadUrl(String path) {
    controller.loadRequest(
      Uri.parse(WpUrls.baseUrl + path),
    );
  }

  void _onItemTapped(int index) {
    setState(() {
      _selectedIndex = index;
      _loadUrl(_pages[index].path);
    });
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
        // In case navigation fails, we should reset the loading state
        setState(() {
          _isLoggingOut = false;
        });
      }
    } else {
      _loadUrl(item.path);
      Navigator.pop(context);
    }
  }

  Future<void> _injectPullToRefreshJS() async {
    final String js =
        await rootBundle.loadString('assets/js/pull_to_refresh.js');
    final String configuredJs =
        js.replaceAll('REFRESH_THRESHOLD', _refreshThreshold.toString());
    await controller.runJavaScript(configuredJs);
  }

  @override
  void initState() {
    super.initState();

    context.read<FCMTokenManagerProvider>().initialize();

    controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setUserAgent('BackMyBracket-MobileApp')
      ..addJavaScriptChannel(
        'Flutter',
        onMessageReceived: (message) {
          if (message.message == 'refresh') {
            controller.reload();
            setState(() => _refreshProgress = 0.0);
          } else if (message.message.startsWith('pull:')) {
            final pullAmount = double.parse(message.message.split(':')[1]);
            setState(() => _refreshProgress =
                (pullAmount / _refreshThreshold).clamp(0.0, 1.0));
          }
        },
      )
      ..setNavigationDelegate(
        NavigationDelegate(
          onProgress: (int progress) {},
          onPageStarted: (String url) {
            setState(() {
              _isLoading = true;
            });

            controller.canGoBack().then((value) {
              setState(() {
                _canGoBack = value;
              });
            });
          },
          onPageFinished: (String url) {
            _injectPullToRefreshJS();
            setAppBarTitle();
            setState(() {
              _isLoading = false;
            });

            controller.canGoBack().then((value) {
              setState(() {
                _canGoBack = value;
              });
            });
          },
          onWebResourceError: (WebResourceError error) {
            debugPrint('Web resource error: ${error.description}');
            setState(() {
              _isLoading = false;
            });
          },
          onNavigationRequest: (NavigationRequest request) async {
            final allowedExternalDomains = [
              'widgets.wp.com',
              'public-api.wordpress.com',
              'wordpress.com'
            ];

            if (!request.url.contains(WpUrls.baseUrl)) {
              final uri = Uri.parse(request.url);

              if (allowedExternalDomains
                  .any((domain) => request.url.contains(domain))) {
                return NavigationDecision.navigate;
              }

              if (await canLaunchUrl(uri)) {
                await launchUrl(uri, mode: LaunchMode.externalApplication);
              }
              return NavigationDecision.prevent;
            }

            if (request.url.contains(WpUrls.loginPath) ||
                request.url.contains('unauthorized')) {
              context.read<AuthProvider>().logout();
              if (mounted) {
                Navigator.pushReplacementNamed(context, '/login');
              }
              return NavigationDecision.prevent;
            }
            return NavigationDecision.navigate;
          },
        ),
      );

    controller
        .loadRequest(Uri.parse('${WpUrls.baseUrl}/dashboard/tournaments/'));

    _startStatusUpdates();
  }

  void _startStatusUpdates() {
    // Update token status every 24 hours
    Future.delayed(const Duration(days: 1), () async {
      if (mounted) {
        await context.read<FCMTokenManagerProvider>().updateStatus();
        _startStatusUpdates(); // Schedule next update
      }
    });
  }

  Future<bool> _handleBackPress() async {
    final canGoBack = await controller.canGoBack();
    if (canGoBack) {
      controller.goBack();
      return false; // Don't close the app
    }
    return true; // Allow closing the app
  }

  void setAppBarTitle() {
    controller.getTitle().then((title) {
      String? trimmedTitle = title?.replaceAll(RegExp(r' - BackMyBracket'), '');
      setState(() {
        _currentTitle = trimmedTitle ?? 'Loading';
      });
    });
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
              title: UpperCaseText(
                _currentTitle,
                style: const TextStyle(color: Colors.white),
              ),
              actions: [
                if (_canGoBack)
                  IconButton(
                    icon: const Icon(Icons.arrow_back),
                    onPressed: () {
                      controller.goBack();
                    },
                    color: Colors.white,
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
                    WebViewWidget(controller: controller),
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
    );
  }
}
