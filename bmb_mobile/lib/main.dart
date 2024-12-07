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
  int _selectedIndex = 0;
  
  static const String baseUrl = 'https://backmybracket.com';

  final List<NavigationItem> _pages = [
    NavigationItem(
      icon: Icons.person,
      label: 'Profile',
      path: '/dashboard/profile',
    ),
    NavigationItem(
      icon: Icons.emoji_events,
      label: 'Tournaments',
      path: '/dashboard/tournaments',
    ),
    NavigationItem(
      icon: Icons.history,
      label: 'History',
      path: '/dashboard/play-history',
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
      path: '/be-a-host',
      icon: Icons.celebration,
    ),
    DrawerItem(
      label: 'BMB Brackets',
      path: '/bmb-brackets',
      icon: Icons.sports_basketball,
    ),
    DrawerItem(
      label: 'Celebrity Picks',
      path: '/celebrity-picks',
      icon: Icons.star,
    ),
    DrawerItem(
      label: 'Shop',
      path: '/shop',
      icon: Icons.shopping_bag,
    ),
    DrawerItem(
      label: 'Referral Program',
      path: '/referralprogram',
      icon: Icons.people,
    ),
    DrawerItem(
      label: 'My Account',
      path: '/dashboard/my-account',
      icon: Icons.settings,
    ),
    DrawerItem(
      label: 'Payments',
      path: '/dashboard/payments',
      icon: Icons.payments,
    ),
    DrawerItem(
      label: 'Logout',
      path: '/wp-login.php?action=logout',
      icon: Icons.logout,
    ),
  ];

  void _onItemTapped(int index) {
    setState(() {
      _selectedIndex = index;
      final path = _pages[index].path;
      controller.loadRequest(Uri.parse(baseUrl + path));
    });
  }

  void _onDrawerItemTap(DrawerItem item) {
    controller.loadRequest(Uri.parse(baseUrl + item.path));
    Navigator.pop(context);
  }

  @override
  void initState() {
    super.initState();
    controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setNavigationDelegate(
        NavigationDelegate(
          onProgress: (int progress) {
            // You could show a loading indicator here
          },
          onPageStarted: (String url) {
            // Inject CSS to hide navigation when page starts loading
            controller.runJavaScript('''
              document.addEventListener('DOMContentLoaded', function() {
                // Hide navigation section
                var nav = document.getElementById('navigation');
                if (nav) {
                  nav.style.display = 'none';
                }
                
                // Hide all nav elements
                var navElements = document.getElementsByTagName('nav');
                for (var i = 0; i < navElements.length; i++) {
                  navElements[i].style.display = 'none';
                }
              });
            ''');
          },
          onPageFinished: (String url) {
            // Also try to hide navigation after page load completes
            controller.runJavaScript('''
              // Hide navigation section
              var nav = document.getElementById('navigation');
              if (nav) {
                nav.style.display = 'none';
              }
              
              // Hide all nav elements
              var navElements = document.getElementsByTagName('nav');
              for (var i = 0; i < navElements.length; i++) {
                navElements[i].style.display = 'none';
              }
            ''');
          },
          onWebResourceError: (WebResourceError error) {
            debugPrint('Web resource error: ${error.description}');
          },
          onNavigationRequest: (NavigationRequest request) {
            return NavigationDecision.navigate;
          },
        ),
      )
      ..loadRequest(Uri.parse(baseUrl));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        backgroundColor: Colors.black,
        iconTheme: const IconThemeData(color: Colors.white),
        title: const Text(
          'Back My Bracket',
          style: TextStyle(color: Colors.white),
        ),
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
          child: WebViewWidget(controller: controller),
        ),
      ),
      bottomNavigationBar: BottomNavigationBar(
        items: _pages
            .map((page) => BottomNavigationBarItem(
                  icon: Icon(page.icon),
                  label: page.label,
                ))
            .toList(),
        currentIndex: _selectedIndex,
        selectedItemColor: Colors.blue,
        type: BottomNavigationBarType.fixed,
        onTap: _onItemTapped,
      ),
    );
  }
}

class NavigationItem {
  final IconData icon;
  final String label;
  final String path;

  NavigationItem({
    required this.icon,
    required this.label,
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
