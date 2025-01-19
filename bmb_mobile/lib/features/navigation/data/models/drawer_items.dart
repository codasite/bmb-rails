import 'package:bmb_mobile/features/navigation/data/models/drawer_item.dart';
import 'package:bmb_mobile/core/utils/asset_paths.dart';

final List<DrawerItem> drawerItems = [
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
