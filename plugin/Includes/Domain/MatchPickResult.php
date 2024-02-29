<?php
namespace WStrategies\BMB\Includes\Domain;

use ValueError;

class MatchPickResult implements BracketMatchNodeInterface {
  public int $round_index;
  public int $match_index;
  public Team $winning_team;
  public Team $losing_team;
  public Team $picked_team;

  public function __construct(array $args = []) {
    $this->round_index = (int) $args['round_index'];
    $this->match_index = (int) $args['match_index'];
    $this->winning_team = $args['winning_team'];
    $this->losing_team = $args['losing_team'];
    $this->picked_team = $args['picked_team'];
    if (
      $this->winning_team->id === null ||
      $this->losing_team->id === null ||
      $this->picked_team->id === null
    ) {
      throw new ValueError('Team id is required');
    }
  }

  public function get_round_index(): int {
    return $this->round_index;
  }

  public function get_match_index(): int {
    return $this->match_index;
  }

  public function correct_picked(): bool {
    return $this->picked_team->id === $this->winning_team->id;
  }
}
