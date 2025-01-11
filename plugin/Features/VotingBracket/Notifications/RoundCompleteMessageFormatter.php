<?php

namespace WStrategies\BMB\Features\VotingBracket\Notifications;

use WStrategies\BMB\Includes\Domain\Bracket;

class RoundCompleteMessageFormatter {
  public static function get_heading(Bracket $bracket): string {
    if ($bracket->status === 'complete') {
      return $bracket->get_title() . ' Voting Complete!';
    }
    return $bracket->get_title() . ' Voting Round Complete!';
  }

  public static function get_message(Bracket $bracket): string {
    if ($bracket->status === 'complete') {
      return 'The voting for ' . $bracket->get_title() . ' is complete!';
    }
    return 'Vote now in round ' . ((int) $bracket->live_round_index + 1);
  }

  public static function get_button_text(Bracket $bracket): string {
    if ($bracket->status === 'complete') {
      return 'View Results';
    }
    return 'Vote now';
  }

  public static function get_button_url_suffix(Bracket $bracket): string {
    if ($bracket->status === 'complete') {
      return 'results';
    }
    return 'play';
  }
}
