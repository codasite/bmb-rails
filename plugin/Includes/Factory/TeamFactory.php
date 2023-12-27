<?php

namespace WStrategies\BMB\Includes\Factory;

use WStrategies\BMB\Includes\Domain\Team;

class TeamFactory implements FactoryInterface {
  public function create(array $data): Team {
    $team = new Team();
    $team->id = isset($args['id']) ? (int) $args['id'] : null;
    $team->name = $args['name'] ?? null;

    return $team;
  }
}
