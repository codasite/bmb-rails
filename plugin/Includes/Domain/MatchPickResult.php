<?php
namespace WStrategies\BMB\Includes\Domain;

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
