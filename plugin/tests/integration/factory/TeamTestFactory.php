<?php

namespace WStrategies\BMB\tests\integration\factory;
use WP_Error;
use WP_UnitTest_Factory_For_Thing;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Repository\BracketTeamRepo;

/**
 * Class WPBB_UnitTest_Factory_For_Team
 *
 * This class is used to create template objects for unit testing
 */
class TeamTestFactory extends WP_UnitTest_Factory_For_Thing {
  private $repo;

  function __construct($factory = null) {
    parent::__construct($factory);
    $this->repo = new BracketTeamRepo();

    $this->default_generation_definitions = ['author' => 1];
  }

  function create_object($args): WP_Error|Team|int|null {
    $team = new Team($args);
    $team = $this->repo->add($team);
    return $team;
  }

  function update_object($team_id, $fields): WP_Error|Team|int|null {
    $team = $this->repo->update($team_id, $fields);
    return $team;
  }

  function get_object_by_id($team_id) {
    return $this->repo->get($team_id);
  }
}
