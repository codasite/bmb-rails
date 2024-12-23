import 'package:bmb_mobile/theme/colors.dart';
import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:bmb_mobile/widgets/upper_case_text.dart';
import 'package:bmb_mobile/models/navigation_item.dart';
import 'package:bmb_mobile/models/drawer_item.dart';
import 'package:bmb_mobile/utils/asset_paths.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
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
      home: const WebViewApp(),
    );
  }
}

class WebViewApp extends StatefulWidget {
  const WebViewApp({super.key});

  @override
  State<WebViewApp> createState() => _WebViewAppState();
}

class _WebViewAppState extends State<WebViewApp> {
  late final WebViewController controller;
  int? _selectedIndex;
  String _currentUrl = '';
  String _currentTitle = 'Back My Bracket';
  bool _isLoading = true;
  bool _canGoBack = false;

  // static const String baseUrl = 'http://backmybracket.test';
  static const String baseUrl = 'https://backmybracket.com';

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
      iconPath: getIconPath('trophy'),
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
      Uri.parse(baseUrl + path),
    );
  }

  void _onItemTapped(int index) {
    setState(() {
      _selectedIndex = index;
      _currentTitle = _pages[index].label;
      _loadUrl(_pages[index].path);
    });
  }

  void _onDrawerItemTap(DrawerItem item) {
    setState(() {
      _currentTitle = item.label;
    });
    _loadUrl(item.path);
    Navigator.pop(context);
  }

  @override
  void initState() {
    super.initState();
    controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setUserAgent('BackMyBracket-MobileApp')
      ..setNavigationDelegate(
        NavigationDelegate(
          onProgress: (int progress) {},
          onPageStarted: (String url) {
            setState(() {
              _currentUrl = url;
              _isLoading = true;
            });

            controller.canGoBack().then((value) {
              setState(() {
                _canGoBack = value;
              });
            });
          },
          onPageFinished: (String url) {
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
          onNavigationRequest: (NavigationRequest request) {
            print('Navigation request: ${request.url}');
            return NavigationDecision.navigate;
          },
        ),
      );

    _loadUrl(_pages[0].path);
    setState(() {
      _selectedIndex = 0;
    });
    _currentTitle = _pages[0].label;
  }

  Future<bool> _handleBackPress() async {
    final canGoBack = await controller.canGoBack();
    if (canGoBack) {
      controller.goBack();
      return false; // Don't close the app
    }
    return true; // Allow closing the app
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
