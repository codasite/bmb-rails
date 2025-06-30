import 'package:bmb_mobile/features/webview/data/models/drawer_item.dart';
import 'package:bmb_mobile/core/utils/asset_paths.dart';

final List<DrawerItem> drawerItems = [
  DrawerItem(
    iconPath: getIconPath('home'),
    label: 'Home',
    path: '/',
  ),
  DrawerItem(
    iconPath: getIconPath('bmb'),
    label: 'Bracket Builder',
    path: '/bracket-builder/',
  ),
  DrawerItem(
    iconPath: getIconPath('currency_dollar'),
    label: 'Shop',
    path: '/shop/',
  ),
  DrawerItem(
    iconPath: getIconPath('shopping_cart'),
    label: 'Cart',
    path: '/cart/',
  ),
  DrawerItem(
    iconPath: getIconPath('user_2'),
    label: 'My Account',
    path: '/dashboard/my-account/',
  ),
  DrawerItem(
    iconPath: getIconPath('lock'),
    label: 'Privacy Policy',
    path: '/privacy-policy/',
  ),
  DrawerItem(
    iconPath: getIconPath('logout'),
    label: 'Logout',
    path: '/wp-login.php?action=logout',
  ),
];
