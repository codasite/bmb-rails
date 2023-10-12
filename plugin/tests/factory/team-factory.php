<?php
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-team.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-team-repo.php';

/**
 * Class WPBB_UnitTest_Factory_For_Team
 *
 * This class is used to create template objects for unit testing
 */
class WPBB_UnitTest_Factory_For_Team extends WP_UnitTest_Factory_For_Thing {
  private $repo;

  function __construct($factory = null) {
    parent::__construct($factory);
    $this->repo = new Wpbb_BracketTeamRepo();

    $this->default_generation_definitions = ['author' => 1];
  }

  function create_object($args) {
    $team = new Wpbb_BracketTeam($args);
    $team = $this->repo->add($team);
    return $team;
  }

  function update_object($team_id, $fields) {
    $team = $this->repo->update($team_id, $fields);
    return $team;
  }

  function get_object_by_id($team_id) {
    return $this->repo->get($team_id);
  }
}
