<?php

namespace WStrategies\BMB\Includes\Factory;

use WStrategies\BMB\Includes\Domain\BracketMatch;

class BracketMatchFactory implements FactoryInterface {
  private TeamFactory $team_factory;
  public function __construct(array $args = []) {
    $this->team_factory = $args['team_factory'] ?? new TeamFactory();
  }

  public function create(array $data): BracketMatch {
    $match = new BracketMatch();
    $match->round_index = (int) $data['round_index'];
    $match->match_index = (int) $data['match_index'];
    $match->team1 = $data['team1'] ?? null;
    $match->team2 = $data['team2'] ?? null;
    $match->id = isset($data['id']) ? (int) $data['id'] : null;

    return $match;
  }
}
