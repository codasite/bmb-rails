<?php
namespace WStrategies\BMB\Includes\Domain;

class MatchPickResult implements BracketMatchNodeInterface {
  public int $round_index;
  public int $match_index;
  public Team $winning_team;
  public Team $losing_team;
  public bool $correct_picked;

  public function __construct(array $args = []) {
    $this->round_index = (int) $args['round_index'];
    $this->match_index = (int) $args['match_index'];
    $this->winning_team = $args['winning_team'];
    $this->losing_team = $args['losing_team'];
    $this->correct_picked = $args['correct_picked'] ?? false;
  }

  public function get_round_index(): int {
    return $this->round_index;
  }

  public function get_match_index(): int {
    return $this->match_index;
  }
}
