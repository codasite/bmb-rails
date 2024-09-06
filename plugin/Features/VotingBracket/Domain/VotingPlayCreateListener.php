<?php

namespace WStrategies\BMB\Features\VotingBracket\Domain;

use WStrategies\BMB\Includes\Controllers\ApiListeners\BracketPlayCreateListenerBase;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Repository\BracketRepo;

class VotingPlayCreateListener extends BracketPlayCreateListenerBase {
  public function __construct(
    private BracketRepo $bracket_repo = new BracketRepo()
  ) {
  }
  public function filter_before_play_added(Play $play): Play {
    // If bracket is voting
    // filter picks to the live round
    $bracket = $this->bracket_repo->get($play->bracket_id);

    if ($bracket->is_voting) {
      $play->picks = array_filter(
        $play->picks,
        fn($pick) => $pick->round_index === $bracket->live_round_index
      );
    }
    return $play;
  }
}
