<?php

namespace WStrategies\BMB\Features\Bracket\UpcomingBracket;

use WStrategies\BMB\Features\Notifications\NotificationSubscription;
use WStrategies\BMB\Includes\Domain\Bracket;

class UpcomingBracketMessageFormatter {
  public static function get_heading(Bracket $bracket): string {
    return strtoupper($bracket->title) . ' is now live. Make your picks!';
  }
}
