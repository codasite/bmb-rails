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
        'Match results not set. Populate match results first using BracketMatchService->matches_2d_from_picks()'
      );
    }
    if (
      $this->get_winning_team()->id === null ||
      $this->get_losing_team()->id === null ||
      $this->get_picked_team()->id === null
    ) {
      throw new ValueError('Team id is required');
    }
  }

  public function picked_team_won(): bool {
    return $this->get_picked_team()->equals($this->get_winning_team());
  }

  public function picked_team_played(): bool {
    return $this->get_picked_team()->equals($this->get_team1()) ||
      $this->get_picked_team()->equals($this->get_team2());
  }

  public function get_picked_team(): Team {
    return $this->pick->get_winning_team();
  }

  public function get_winning_team(): Team {
    return $this->match->get_winning_team();
  }

  public function get_losing_team(): Team {
    return $this->match->get_losing_team();
  }

  public function get_team1(): Team {
    return $this->match->team1;
  }

  public function get_team2(): Team {
    return $this->match->team2;
  }
}
