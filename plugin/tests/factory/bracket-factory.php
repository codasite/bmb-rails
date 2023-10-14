<?php
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-bracket.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-repo.php';

/**
 * Class WPBB_UnitTest_Factory_For_Play
 *
 * This class is used to create template objects for unit testing
 */
class WPBB_UnitTest_Factory_For_Bracket extends WP_UnitTest_Factory_For_Thing {
  private $bracket_repo;

  function __construct($factory = null) {
    parent::__construct($factory);
    $this->bracket_repo = new Wpbb_BracketRepo();

    $this->default_generation_definitions = [
      'title' => new WP_UnitTest_Generator_Sequence('Bracket %s'),
      'author' => 1,
    ];
  }

  function create_object($args) {
    $bracket = new Wpbb_Bracket($args);
    $bracket = $this->bracket_repo->add($bracket);
    return $bracket;
  }

  function update_object($bracket_id, $fields) {
    $bracket = $this->bracket_repo->update($bracket_id, $fields);
    return $bracket;
  }

  function get_object_by_id($bracket_id) {
    return $this->bracket_repo->get($bracket_id);
  }
}
