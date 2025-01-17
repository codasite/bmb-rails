import 'package:bmb_mobile/login/wp_auth.dart';
import 'package:firebase_core/firebase_core.dart';
import 'firebase_options.dart';
import 'package:bmb_mobile/theme/bmb_colors.dart';
import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:bmb_mobile/widgets/upper_case_text.dart';
import 'package:bmb_mobile/models/navigation_item.dart';
import 'package:bmb_mobile/models/drawer_item.dart';
import 'package:bmb_mobile/utils/asset_paths.dart';
import 'package:bmb_mobile/login/login_screen.dart';
import 'package:bmb_mobile/constants.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:flutter/services.dart';
import 'package:flutter_native_splash/flutter_native_splash.dart';
import 'package:bmb_mobile/services/fcm_token_service.dart';
import 'package:bmb_mobile/utils/app_logger.dart';
import 'package:flutter_dotenv/flutter_dotenv.dart';

void main() async {
  WidgetsBinding widgetsBinding = WidgetsFlutterBinding.ensureInitialized();
  FlutterNativeSplash.preserve(widgetsBinding: widgetsBinding);

  // Load environment variables
  await dotenv.load();

  await AppLogger.initialize(
    dsn: dotenv.env['SENTRY_DSN'] ?? '',
    environment: dotenv.env['SENTRY_ENV'] ?? 'development',
  );

  await Firebase.initializeApp(
    options: DefaultFirebaseOptions.currentPlatform,
  );

  await SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
    DeviceOrientation.portraitDown,
  ]);

  // Check authentication status before launching app
  final authService = WpAuth();
  final isAuthenticated = await authService.isAuthenticated();

  // Remove splash screen and launch app
  runApp(MyApp(isAuthenticated: isAuthenticated));
}

class MyApp extends StatelessWidget {
  final bool isAuthenticated;

  const MyApp({super.key, required this.isAuthenticated});

  @override
  Widget build(BuildContext context) {
    // Remove splash screen after a delay
    Future.delayed(const Duration(seconds: 1), () {
      FlutterNativeSplash.remove();
    });

    return MaterialApp(
      title: 'Back My Bracket',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.blue),
        useMaterial3: true,
        fontFamily: 'ClashDisplay',
        textTheme: const TextTheme(
          bodyLarge: TextStyle(fontSize: 16.0),
          bodyMedium: TextStyle(fontSize: 14.0),
          titleLarge: TextStyle(fontSize: 20.0),
        ),
      ),
      routes: {
        '/app': (context) => const WebViewApp(),
        '/login': (context) => const LoginScreen(),
      },
      initialRoute: isAuthenticated ? '/app' : '/login',
    );
  }
}

class WebViewApp extends StatefulWidget {
  const WebViewApp({super.key});

  @override
  State<WebViewApp> createState() => _WebViewAppState();
}

class _WebViewAppState extends State<WebViewApp> {
  static const double _refreshThreshold = 65.0;

  late final WebViewController controller;
  late final FCMTokenService _fcmService;
  int? _selectedIndex;
  String _currentTitle = 'Back My Bracket';
  bool _isLoading = true;
  bool _canGoBack = false;
  double _refreshProgress = 0.0;

  final List<NavigationItem> _pages = [
    NavigationItem(
      iconPath: getIconPath('user'),
      shortLabel: 'Profile',
      label: 'My Profile',
      path: '/dashboard/profile/',
      slug: 'profile',
    ),
    NavigationItem(
      iconPath: getIconPath('signal'),
      shortLabel: 'Tournaments',
      label: 'My Tournaments',
      path: '/dashboard/tournaments/',
      slug: 'tournaments',
    ),
    NavigationItem(
      iconPath: getIconPath('clock'),
      shortLabel: 'History',
      label: 'My Play History',
      path: '/dashboard/play-history/',
      slug: 'play-history',
    ),
  ];

  final List<DrawerItem> _drawerItems = [
    DrawerItem(
      iconPath: getIconPath('home'),
      label: 'Home',
      path: '/',
    ),
    DrawerItem(
      iconPath: getIconPath('currency_dollar'),
      label: 'Be a Host',
      path: '/be-a-host/',
    ),
    DrawerItem(
      iconPath: getIconPath('bmb'),
      label: 'BMB Brackets',
      path: '/bmb-brackets/',
    ),
    DrawerItem(
      iconPath: getIconPath('eye'),
      label: 'Celebrity Picks',
      path: '/celebrity-picks/',
    ),
    DrawerItem(
      iconPath: getIconPath('shopping_cart'),
      label: 'Shop',
      path: '/shop/',
    ),
    DrawerItem(
      iconPath: getIconPath('ticket'),
      label: 'Referral Program',
      path: '/referralprogram/',
    ),
    DrawerItem(
      iconPath: getIconPath('user_2'),
      label: 'My Account',
      path: '/dashboard/my-account/',
    ),
    DrawerItem(
      iconPath: getIconPath('logout'),
      label: 'Logout',
      path: '/wp-login.php?action=logout',
    ),
  ];

  void _loadUrl(String path) {
    controller.loadRequest(
      Uri.parse(AppConstants.baseUrl + path),
    );
  }

  void _onItemTapped(int index) {
    setState(() {
      _selectedIndex = index;
      // _currentTitle = _pages[index].label;
      _loadUrl(_pages[index].path);
    });
  }

  void _onDrawerItemTap(DrawerItem item) async {
    if (item.path == '/wp-login.php?action=logout') {
      await _fcmService.deregisterToken();
      await WpAuth().logout();
      if (mounted) {
        Navigator.pushReplacementNamed(context, '/login');
      }
    } else {
      _loadUrl(item.path);
      Navigator.pop(context);
    }
  }

  @override
  void initState() {
    super.initState();

    // Initialize FCM service
    _fcmService = FCMTokenService();
    _initializeFCM();

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
            // Parse pull amount from message
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
            // Inject overscroll detection JavaScript
            controller.runJavaScript('''
              let startY;
              document.addEventListener('touchstart', (e) => {
                startY = e.touches[0].pageY;
              });
              document.addEventListener('touchmove', (e) => {
                const y = e.touches[0].pageY;
                const scrollTop = document.documentElement.scrollTop;
                
                if (scrollTop === 0) {
                  const pullAmount = y - startY;
                  if (pullAmount > ${_refreshThreshold}) {
                    Flutter.postMessage('refresh');
                  } else if (pullAmount > 0) {
                    Flutter.postMessage('pull:' + pullAmount);
                  }
                }
              });
              document.addEventListener('touchend', () => {
                Flutter.postMessage('pull:0');
              });
            ''');

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
            // List of allowed external domains for system functionality
            final allowedExternalDomains = [
              'widgets.wp.com',
              'public-api.wordpress.com',
              'wordpress.com'
            ];

            // Check if URL is external (not our domain)
            if (!request.url.contains(AppConstants.baseUrl)) {
              final uri = Uri.parse(request.url);

              // Allow system-related external requests to load in WebView
              if (allowedExternalDomains
                  .any((domain) => request.url.contains(domain))) {
                return NavigationDecision.navigate;
              }

              // Open other external links in browser
              if (await canLaunchUrl(uri)) {
                await launchUrl(uri, mode: LaunchMode.externalApplication);
              }
              return NavigationDecision.prevent;
            }

            // Handle unauthorized/login redirects
            if (request.url.contains(AppConstants.loginPath) ||
                request.url.contains('unauthorized')) {
              WpAuth().logout();
              if (mounted) {
                Navigator.pushReplacementNamed(context, '/login');
              }
              return NavigationDecision.prevent;
            }
            return NavigationDecision.navigate;
          },
        ),
      );

    // Since this widget only mounts when user is authenticated,
    // we can safely load the initial URL
    controller.loadRequest(
        Uri.parse('${AppConstants.baseUrl}/dashboard/tournaments/'));

    // Start periodic status updates
    _startStatusUpdates();
  }

  Future<void> _initializeFCM() async {
    await _fcmService.initialize();
    await _fcmService.setupToken();
  }

  void _startStatusUpdates() {
    // Update token status every 24 hours
    Future.delayed(const Duration(days: 1), () async {
      await _fcmService.updateStatus();
      _startStatusUpdates(); // Schedule next update
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
      child: Scaffold(
        backgroundColor: BMBColors.ddBlue,
        appBar: AppBar(
          backgroundColor: BMBColors.darkBlue,
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
        drawer: Drawer(
            backgroundColor: BMBColors.darkBlue,
            child: SafeArea(
              child: ListView(
                padding: EdgeInsets.zero,
                children: [
                  ListTile(
                    leading: const Icon(
                      Icons.close,
                      color: Colors.white,
                      size: 24,
                    ),
                    title: UpperCaseText(
                      'Close',
                      style: const TextStyle(color: Colors.white),
                    ),
                    onTap: () => Navigator.pop(context),
                  ),
                  // Add logo header
                  InkWell(
                    onTap: () => _onDrawerItemTap(DrawerItem(
                      iconPath: getIconPath('home'),
                      label: 'Home',
                      path: '/',
                    )),
                    child: Container(
                      padding: const EdgeInsets.only(
                        left: 16,
                        top: 30,
                        bottom: 30,
                      ),
                      alignment: Alignment.centerLeft,
                      child: SvgPicture.asset(
                        getIconPath('bmb_logo'),
                        height: 40,
                      ),
                    ),
                  ),
                  // Add close button
                  ..._drawerItems.map((item) => ListTile(
                        title: UpperCaseText(item.label),
                        textColor: Colors.white,
                        onTap: () => _onDrawerItemTap(item),
                        leading: SvgPicture.asset(
                          item.iconPath,
                          width: 24,
                          height: 24,
                          colorFilter: const ColorFilter.mode(
                            Colors.white,
                            BlendMode.srcIn,
                          ),
                        ),
                      )),
                ],
              ),
            )),
        body: SafeArea(
          child: Container(
            color: BMBColors.ddBlue,
            child: Stack(
              children: [
                WebViewWidget(controller: controller),
                if (_isLoading)
                  Container(
                    color: Colors.transparent.withOpacity(0.5),
                    child: const Center(
                      child: CircularProgressIndicator(
                        color: BMBColors.blue,
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
                        color: BMBColors.blue,
                        size: 24,
                      ),
                    ),
                  ),
              ],
            ),
          ),
        ),
        bottomNavigationBar: Container(
          padding: const EdgeInsets.only(top: 10),
          color: BMBColors.darkBlue,
          child: BottomNavigationBar(
            elevation: 0,
            backgroundColor: Colors.transparent,
            items: _pages
                .map((page) => BottomNavigationBarItem(
                      icon: Padding(
                        padding: const EdgeInsets.only(bottom: 4),
                        child: SvgPicture.asset(
                          page.iconPath,
                          width: 24,
                          height: 24,
                          colorFilter: const ColorFilter.mode(
                            Colors.white,
                            BlendMode.srcIn,
                          ),
                        ),
                      ),
                      label: page.shortLabel.toUpperCase(),
                    ))
                .toList(),
            currentIndex: _selectedIndex ?? 0,
            type: BottomNavigationBarType.fixed,
            selectedLabelStyle: const TextStyle(fontSize: 12),
            unselectedLabelStyle: const TextStyle(fontSize: 12),
            selectedItemColor: BMBColors.white,
            unselectedItemColor: BMBColors.white,
            onTap: _onItemTapped,
          ),
        ),
      ),
    );
  }
}
