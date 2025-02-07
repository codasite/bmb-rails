import 'package:bmb_mobile/core/theme/bmb_colors.dart';
import 'package:bmb_mobile/core/theme/bmb_font_weights.dart';
import 'package:bmb_mobile/core/utils/app_logger.dart';
import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';
import 'package:bmb_mobile/core/widgets/upper_case_text.dart';
import 'package:bmb_mobile/features/webview/data/models/navigation_item.dart';
import 'package:bmb_mobile/features/webview/data/models/drawer_item.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:provider/provider.dart';
import 'package:bmb_mobile/features/wp_auth/presentation/providers/auth_provider.dart';
import 'package:bmb_mobile/features/notifications/presentation/providers/fcm_token_manager_provider.dart';
import 'package:bmb_mobile/features/webview/config/bottom_nav_items.dart';
import 'package:flutter/services.dart' show rootBundle;
import 'package:bmb_mobile/features/wp_http/wp_urls.dart';
import 'package:bmb_mobile/features/webview/presentation/widgets/bmb_drawer.dart';
import 'package:bmb_mobile/features/webview/presentation/widgets/bmb_bottom_nav_bar.dart';
import 'package:bmb_mobile/features/webview/presentation/providers/webview_provider.dart';
import 'dart:async';
import 'package:bmb_mobile/features/notifications/presentation/screens/notification_screen.dart';
import 'package:bmb_mobile/features/notifications/presentation/providers/notification_provider.dart';

class WebViewScreen extends StatefulWidget {
  const WebViewScreen({super.key});

  @override
  State<WebViewScreen> createState() => _WebViewScreenState();
}

class _WebViewScreenState extends State<WebViewScreen> {
  static const double _refreshThreshold = 65.0;

  int? _selectedIndex;
  String _currentTitle = 'Back My Bracket';
  double _refreshProgress = 0.0;
  bool _isLoggingOut = false;

  final List<NavigationItem> _pages = bottomNavItems;

  void _onItemTapped(int index) {
    setState(() {
      _selectedIndex = index;
    });
    context.read<WebViewProvider>().loadUrl(_pages[index].path);
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
      await context.read<WebViewProvider>().loadUrl(item.path);
      Navigator.pop(context);
    }
  }

  Future<void> _injectPullToRefreshJS() async {
    final String js =
        await rootBundle.loadString('assets/js/pull_to_refresh.js');
    final String configuredJs =
        js.replaceAll('REFRESH_THRESHOLD', _refreshThreshold.toString());
    await context
        .read<WebViewProvider>()
        .controller
        .runJavaScript(configuredJs);
  }

  void _handleNotificationNavigation() async {
    final result = await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => const NotificationScreen(),
      ),
    );

    if (mounted && result != null && result is String) {
      context.read<WebViewProvider>().loadUrl(result, prependBaseUrl: false);
    }
  }

  @override
  void initState() {
    super.initState();
    _setupWebView();
  }

  Future<void> _setupWebView() async {
    if (!mounted) return;

    final webViewProvider = context.read<WebViewProvider>();
    await webViewProvider.addJavaScriptChannel(
      'Flutter',
      (message) {
        if (message.message == 'refresh') {
          webViewProvider.controller.reload();
          setState(() => _refreshProgress = 0.0);
        } else if (message.message.startsWith('pull:')) {
          final pullAmount = double.parse(message.message.split(':')[1]);
          setState(() => _refreshProgress =
              (pullAmount / _refreshThreshold).clamp(0.0, 1.0));
        }
      },
    );

    webViewProvider.controller.setNavigationDelegate(
      NavigationDelegate(
        onProgress: (int progress) {},
        onPageStarted: (String url) {
          webViewProvider.setLoading(true);
          webViewProvider.controller.canGoBack().then((value) {
            if (mounted) {
              webViewProvider.setCanGoBack(value);
            }
          });
        },
        onPageFinished: (String url) {
          _injectPullToRefreshJS();
          setAppBarTitle();
          webViewProvider.setLoading(false);
          webViewProvider.controller.canGoBack().then((value) {
            if (mounted) {
              webViewProvider.setCanGoBack(value);
            }
          });
        },
        onWebResourceError: (WebResourceError error) {
          debugPrint('Web resource error: ${error.description}');
          webViewProvider.setLoading(false);
        },
        onNavigationRequest: (NavigationRequest request) async {
          final uri = Uri.parse(request.url);

          // If it's our domain, allow navigation
          if (request.url.contains(WpUrls.baseUrl)) {
            // Check for login/unauthorized paths
            if (request.url.contains(WpUrls.loginPath) ||
                request.url.contains('unauthorized')) {
              context.read<AuthProvider>().logout();
              if (mounted) {
                Navigator.pushReplacementNamed(context, '/login');
              }
              return NavigationDecision.prevent;
            }
            return NavigationDecision.navigate;
          }

          // Check if this is a resource request (not a page navigation)
          final isResource = uri.path.toLowerCase().contains(RegExp(
                r'\.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot|map)$',
              ));

          final allowedExternalDomains = [
            'm.stripe.network',
            'js.stripe.com',
            'widgets.wp.com',
            'public-api.wordpress.com',
            'wordpress.com'
          ];

          final isAllowedDomain = allowedExternalDomains
              .any((domain) => request.url.contains(domain));

          // Allow resource requests to load in WebView
          if (isResource || isAllowedDomain) {
            return NavigationDecision.navigate;
          }

          // For all other external URLs, open in external browser
          if (await canLaunchUrl(uri)) {
            AppLogger.debugLog(
              'Launching external domain: ${request.url}',
            );
            await launchUrl(uri, mode: LaunchMode.externalApplication);
          }
          return NavigationDecision.prevent;
        },
      ),
    );

    await webViewProvider.loadUrl('/dashboard/tournaments/');
  }

  Future<bool> _handleBackPress() async {
    return !(await context.read<WebViewProvider>().goBack());
  }

  void setAppBarTitle() {
    context.read<WebViewProvider>().controller.getTitle().then((title) {
      String? trimmedTitle = title?.replaceAll(RegExp(r' - BackMyBracket'), '');
      setState(() {
        _currentTitle = trimmedTitle ?? 'Loading';
      });
    });
  }

  @override
  Widget build(BuildContext context) {
    final webViewProvider = context.watch<WebViewProvider>();

    return PopScope<Object?>(
      canPop: !webViewProvider.canGoBack,
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
                if (webViewProvider.canGoBack)
                  IconButton(
                    icon: const Icon(Icons.arrow_back),
                    onPressed: () {
                      webViewProvider.goBack();
                    },
                    color: Colors.white,
                  ),
                Padding(
                  padding: const EdgeInsets.only(right: 8),
                  child: IconButton(
                    icon: Badge(
                      isLabelVisible:
                          context.watch<NotificationProvider>().unreadCount > 0,
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
                    WebViewWidget(controller: webViewProvider.controller),
                    if (webViewProvider.isLoading)
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
