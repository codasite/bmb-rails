import 'dart:ui';

// Needed because FontWeight class does not work with variable fonts
// Use like so:
// Text('Hello', style: TextStyle(fontVariations: BMBFontWeight.w700))

class BmbFontWeights {
  static List<FontVariation> w100 = [const FontVariation('wght', 100)];
  static List<FontVariation> w200 = [const FontVariation('wght', 200)];
  static List<FontVariation> w300 = [const FontVariation('wght', 300)];
  static List<FontVariation> w400 = [const FontVariation('wght', 400)];
  static List<FontVariation> w500 = [const FontVariation('wght', 500)];
  static List<FontVariation> w600 = [const FontVariation('wght', 600)];
  static List<FontVariation> w700 = [const FontVariation('wght', 700)];
}
