class NavigationItem {
  final String iconPath;
  final String label;
  final String shortLabel;
  final String path;
  final bool isInitial;

  NavigationItem({
    required this.iconPath,
    required this.label,
    required this.shortLabel,
    required this.path,
    this.isInitial = false,
  });
}
