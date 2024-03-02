<?php
namespace WStrategies\BMB\Includes\Domain;

use ValueError;

/**
 * The result of a pick.
 */
class PickResult {
  public function __construct(
    public readonly BracketMatch $match,
    private Pick $pick
  ) {
    if ($match->get_round_index() !== $pick->get_round_index()) {
      throw new ValueError('Round index mismatch');
    }
    if ($match->get_match_index() !== $pick->get_match_index()) {
      throw new ValueError('Match index mismatch');
    }
    if (!$match->has_results()) {
      throw new ValueError(
        'Match results not set. Populate match results first using BracketMatchService->matches_from_picks()'
      );
    }
    if (
      $this->match->get_winning_team()->id === null ||
      $this->match->get_losing_team()->id === null ||
      $this->get_picked_team()->id === null
    ) {
      throw new ValueError('Team id is required');
    }
  }

  public function correct_picked(): bool {
    return $this->get_picked_team()->id ===
      $this->match->get_winning_team()->id;
  }

  public function get_picked_team(): Team {
    return $this->pick->winning_team;
  }
}
