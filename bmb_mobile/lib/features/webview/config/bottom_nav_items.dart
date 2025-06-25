import 'package:bmb_mobile/features/webview/data/models/navigation_item.dart';
import 'package:bmb_mobile/core/utils/asset_paths.dart';

final List<NavigationItem> bottomNavItems = [
  NavigationItem(
    iconPath: getIconPath('user'),
    shortLabel: 'Profile',
    label: 'My Profile',
    path: '/dashboard/profile/',
  ),
  NavigationItem(
    iconPath: getIconPath('trophy'),
    shortLabel: 'Tournaments',
    label: 'Tournaments',
    path: '/tournaments/',
    isInitial: true,
  ),
  NavigationItem(
    iconPath: getIconPath('signal'),
    shortLabel: 'My Brackets',
    label: 'My Brackets',
    path: '/dashboard/tournaments/',
  ),
  // NavigationItem(
  //   iconPath: getIconPath('clock'),
  //   shortLabel: 'History',
  //   label: 'My Play History',
  //   path: '/dashboard/play-history/',
  // ),
];
