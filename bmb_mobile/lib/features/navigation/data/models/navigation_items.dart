import 'package:bmb_mobile/features/navigation/data/models/navigation_item.dart';
import 'package:bmb_mobile/core/utils/asset_paths.dart';

final List<NavigationItem> navigationItems = [
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
