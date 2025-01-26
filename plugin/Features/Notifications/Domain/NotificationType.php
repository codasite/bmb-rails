<?php

namespace WStrategies\BMB\Features\Notifications\Domain;

use ValueError;

enum NotificationType: string {
  case BRACKET_UPCOMING = 'bracket_upcoming';
  case BRACKET_RESULTS = 'bracket_results';
  case ROUND_COMPLETE = 'round_complete';
  case TOURNAMENT_START = 'tournament_start';

  public static function is_valid(string $value): bool {
    try {
      NotificationType::from($value);
      return true;
    } catch (ValueError $e) {
      return false;
    }
  }
}
