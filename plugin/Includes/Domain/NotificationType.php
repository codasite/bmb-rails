<?php

namespace WStrategies\BMB\Includes\Domain;

use ValueError;

enum NotificationType: string {
  case BRACKET_UPCOMING = 'bracket_upcoming';

  public static function is_valid(string $value): bool {
    try {
      NotificationType::from($value);
      return true;
    } catch (ValueError $e) {
      return false;
    }
  }
}
