<?php

namespace WStrategies\BMB\Includes\Domain;

/**
 * Constants for wildcard placement options in brackets.
 */
class WildcardPlacement {
  const TOP = 0;
  const BOTTOM = 1;
  const CENTER = 2;
  const SPLIT = 3;

  const OPTIONS = [
    'top' => self::TOP,
    'bottom' => self::BOTTOM,
    'center' => self::CENTER,
    'split' => self::SPLIT,
  ];
}
