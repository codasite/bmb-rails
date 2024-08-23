<?php

namespace WStrategies\BMB\Features\VotingBracket;

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Repository\PickRepo;

class VotingBracketService {
  public function __construct(private PickRepo $pick_repo) {
  }

  /**
   * Returns whether the bracket has plays for the live round.
   * @return bool
   */
  public function has_plays_for_live_round(Bracket $bracket): bool {
    $num_picks = $this->pick_repo->get_num_picks_for_round(
      $bracket->id,
      $bracket->live_round_index
    );
    return $num_picks > 0;
  }
}
