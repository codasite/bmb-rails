<?php

namespace WStrategies\BMB\Features\Bracket\UpcomingBracket;

use WStrategies\BMB\Features\Notifications\Domain\NotificationSubscription;
use WStrategies\BMB\Includes\Domain\Bracket;

class UpcomingBracketMessageFormatter {
  public static function get_message(Bracket $bracket): string {
    return strtoupper($bracket->title) . ' is now live. Make your picks!';
  }

  public static function get_title(): string {
    return 'Tournament is now live!';
  }

  public static function get_link(Bracket $bracket): string {
    return $bracket->url;
  }
}
