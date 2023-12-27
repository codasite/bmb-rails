<?php

namespace WStrategies\BMB\Includes\Factory;

use WStrategies\BMB\Includes\Domain\MatchPick;

class MatchPickFactory implements FactoryInterface {
  private TeamFactory $team_factory;

  public function __construct(array $data = []) {
    $this->team_factory = $data['team_factory'] ?? new TeamFactory();
  }
  public function create(array $data): MatchPick {
    $pick = new MatchPick();
    $pick->round_index = (int) $data['round_index'];
    $pick->match_index = (int) $data['match_index'];
    $pick->winning_team_id = (int) $data['winning_team_id'];
    $pick->winning_team = $data['winning_team'] ?? null;
    $pick->id = isset($data['id']) ? (int) $data['id'] : null;

    return $pick;
  }
}
