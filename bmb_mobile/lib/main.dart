import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';

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

  static const String baseUrl = 'http://backmybracket.test';
  // static const String baseUrl = 'https://backmybracket.com';

  final List<NavigationItem> _pages = [
    NavigationItem(
      icon: Icons.person,
      shortLabel: 'Profile',
      label: 'My Profile',
      path: '/dashboard/profile/',
    ),
    NavigationItem(
      icon: Icons.emoji_events,
      shortLabel: 'Tournaments',
      label: 'My Tournaments',
      path: '/dashboard/tournaments/',
    ),
    NavigationItem(
      icon: Icons.history,
      shortLabel: 'History',
      label: 'My Play History',
      path: '/dashboard/play-history/',
    ),
  ];

  final List<DrawerItem> _drawerItems = [
    DrawerItem(
      label: 'Home',
      path: '/',
      icon: Icons.home,
    ),
    DrawerItem(
      label: 'Be a Host',
      path: '/be-a-host/',
      icon: Icons.celebration,
    ),
    DrawerItem(
      label: 'BMB Brackets',
      path: '/bmb-brackets/',
      icon: Icons.sports_basketball,
    ),
    DrawerItem(
      label: 'Celebrity Picks',
      path: '/celebrity-picks/',
      icon: Icons.star,
    ),
    DrawerItem(
      label: 'Shop',
      path: '/shop/',
      icon: Icons.shopping_bag,
    ),
    DrawerItem(
      label: 'Referral Program',
      path: '/referralprogram/',
      icon: Icons.people,
    ),
    DrawerItem(
      label: 'My Account',
      path: '/dashboard/my-account/',
      icon: Icons.settings,
    ),
    DrawerItem(
      label: 'Payments',
      path: '/dashboard/payments/',
      icon: Icons.payments,
    ),
    DrawerItem(
      label: 'Logout',
      path: '/wp-login.php?action=logout',
      icon: Icons.logout,
    ),
  ];

  void _loadUrl(String path) {
    controller.loadRequest(
      Uri.parse(baseUrl + path),
      headers: {
        'X-BMB-MOBILE-APP': 'true',
      },
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

  void _syncNavigationState() {
    final currentPath = Uri.parse(_currentUrl).path;
    setState(() {
      _selectedIndex = _pages.indexWhere((page) => page.path == currentPath);
      if (_selectedIndex == -1) {
        _selectedIndex = null;
        final drawerItem = _drawerItems.firstWhere(
          (item) => item.path == currentPath,
          orElse: () => DrawerItem(
            label: 'Back My Bracket',
            path: '',
            icon: Icons.home,
          ),
        );
        _currentTitle = drawerItem.label;
      } else {
        _currentTitle = _pages[_selectedIndex!].label;
      }
    });
  }

  @override
  void initState() {
    super.initState();
    controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      // ..clearCache() // TODO: Remove this before release
      ..setNavigationDelegate(
        NavigationDelegate(
          onProgress: (int progress) {},
          onPageStarted: (String url) {
            setState(() {
              _currentUrl = url;
              _isLoading = true;
            });
            _syncNavigationState();

            // Update back navigation state
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

            // Update back navigation state
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

    // Initial load - changed from empty string to profile path
    _loadUrl('/dashboard/profile');

    // Set initial selected index to Profile (0)
    setState(() {
      _selectedIndex = 0;
    });

    // Set initial title to Profile
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
        backgroundColor: Colors.black,
        appBar: AppBar(
          backgroundColor: Colors.black,
          iconTheme: const IconThemeData(color: Colors.white),
          leading: IconButton(
            icon: const Icon(Icons.menu),
            onPressed: () {
              Scaffold.of(context).openDrawer();
            },
          ),
          title: Text(
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
          child: ListView(
            padding: EdgeInsets.zero,
            children: [
              const DrawerHeader(
                decoration: BoxDecoration(
                  color: Colors.blue,
                ),
                child: Text(
                  'Menu',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 24,
                  ),
                ),
              ),
              ..._drawerItems.map((item) => ListTile(
                    leading: Icon(item.icon),
                    title: Text(item.label),
                    onTap: () => _onDrawerItemTap(item),
                  )),
            ],
          ),
        ),
        body: SafeArea(
          child: Container(
            color: Colors.black,
            child: Stack(
              children: [
                WebViewWidget(controller: controller),
                if (_isLoading)
                  Container(
                    color: Colors.transparent.withOpacity(0.5),
                    child: const Center(
                      child: CircularProgressIndicator(
                        color: Colors.blue,
                      ),
                    ),
                  ),
              ],
            ),
          ),
        ),
        bottomNavigationBar: BottomNavigationBar(
          items: _pages
              .map((page) => BottomNavigationBarItem(
                    icon: Icon(page.icon),
                    label: page.shortLabel,
                  ))
              .toList(),
          currentIndex: _selectedIndex ?? 0,
          selectedItemColor: _selectedIndex == null ? Colors.grey : Colors.blue,
          type: BottomNavigationBarType.fixed,
          onTap: _onItemTapped,
        ),
      ),
    );
  }
}

class NavigationItem {
  final IconData icon;
  final String label;
  final String shortLabel;
  final String path;

  NavigationItem({
    required this.icon,
    required this.label,
    required this.shortLabel,
    required this.path,
  });
}

class DrawerItem {
  final String label;
  final String path;
  final IconData icon;

  DrawerItem({
    required this.label,
    required this.path,
    required this.icon,
  });
}
