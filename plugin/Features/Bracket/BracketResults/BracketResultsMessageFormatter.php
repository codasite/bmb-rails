<?php

namespace WStrategies\BMB\Features\Bracket\BracketResults;

use WStrategies\BMB\Includes\Domain\PickResult;
use WStrategies\BMB\Includes\Domain\Play;

class BracketResultsMessageFormatter {
  public static function get_message(PickResult $result): string {
    $picked_team = strtoupper($result->get_picked_team()->name);
    $winning_team = strtoupper($result->match->get_winning_team()->name);

    if ($result->picked_team_won()) {
      return 'You picked ' . $picked_team . '... and they won!';
    } else {
      return 'You picked ' .
        $picked_team .
        '... but ' .
        $winning_team .
        ' won the round!';
    }
  }
  public static function get_title(): string {
    return 'Bracket Results Updated';
  }

  public static function get_link(Play $play): string {
    return $play->url . 'view';
  }
}
