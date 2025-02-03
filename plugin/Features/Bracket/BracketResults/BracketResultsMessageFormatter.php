<?php

namespace WStrategies\BMB\Features\Bracket\BracketResults;

use WStrategies\BMB\Includes\Domain\PickResult;

class BracketResultsMessageFormatter {
  public static function get_heading(PickResult $result): string {
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
}
