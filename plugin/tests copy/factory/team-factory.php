<?php
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-team.php';
require_once WPBB_PLUGIN_DIR .
  'includes/domain/class-wpbb-bracket-template.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-template-repo.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-team-repo.php';

/**
 * Class WPBB_UnitTest_Factory_For_Team
 *
 * This class is used to create team objects for unit testing
 */
class WPBB_UnitTest_Factory_For_Team extends WP_UnitTest_Factory_For_Thing {
  private $team_repo;

  function __construct($factory = null) {
    parent::__construct($factory);
    $this->team_repo = new Wpbb_BracketTeamRepo();

    $this->default_generation_definitions = [
      'name' => new WP_UnitTest_Generator_Sequence('Team %s'),
    ];
  }

  function create_object($args) {
    $team = new Wpbb_Team($args);
    $template_id = $args['bracket_template_id'] ?? null;
    $team = $this->team_repo->add($template_id, $team);
    return $team;
  }

  function update_object($team_id, $fields) {
    $team = $this->team_repo->update($team_id, $fields);
    return $team;
  }

  function get_object_by_id($team_id) {
    return $this->team_repo->get($team_id);
  }
}
