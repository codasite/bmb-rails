import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:bmb_mobile/core/theme/bmb_colors.dart';
import 'package:bmb_mobile/core/widgets/upper_case_text.dart';
import 'package:bmb_mobile/features/webview/data/models/drawer_item.dart';
import 'package:bmb_mobile/features/webview/config/drawer_items.dart';
import 'package:bmb_mobile/core/utils/asset_paths.dart';

class BmbDrawer extends StatelessWidget {
  final Function(DrawerItem) onDrawerItemTap;

  const BmbDrawer({
    super.key,
    required this.onDrawerItemTap,
  });

  @override
  Widget build(BuildContext context) {
    return Drawer(
      backgroundColor: BmbColors.darkBlue,
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
            InkWell(
              onTap: () => onDrawerItemTap(DrawerItem(
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
            ...drawerItems.map((item) => ListTile(
                  title: UpperCaseText(item.label),
                  textColor: Colors.white,
                  onTap: () => onDrawerItemTap(item),
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
      ),
    );
  }
}
