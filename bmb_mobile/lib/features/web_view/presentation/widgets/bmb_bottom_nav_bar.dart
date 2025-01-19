import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:bmb_mobile/core/theme/bmb_colors.dart';
import 'package:bmb_mobile/features/web_view/data/models/navigation_item.dart';

class BmbBottomNavBar extends StatelessWidget {
  final List<NavigationItem> pages;
  final int selectedIndex;
  final Function(int) onItemTapped;

  const BmbBottomNavBar({
    super.key,
    required this.pages,
    required this.selectedIndex,
    required this.onItemTapped,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.only(top: 10),
      color: BmbColors.darkBlue,
      child: BottomNavigationBar(
        elevation: 0,
        backgroundColor: Colors.transparent,
        items: pages
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
        currentIndex: selectedIndex,
        type: BottomNavigationBarType.fixed,
        selectedLabelStyle: const TextStyle(fontSize: 12),
        unselectedLabelStyle: const TextStyle(fontSize: 12),
        selectedItemColor: BmbColors.white,
        unselectedItemColor: BmbColors.white,
        onTap: onItemTapped,
      ),
    );
  }
}
