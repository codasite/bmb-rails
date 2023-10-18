<?php
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-bracket.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-repo.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-match.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-team.php';

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
    if (isset($args['num_teams']) && !isset($args['matches'])) {
      $args['matches'] = $this->generateMatches($args['num_teams']);
    }
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

  function generateMatches($numberOfTeams) {
    $matches = [];

    $num_matches = $numberOfTeams / 2;

    for ($i = 0; $i < $num_matches; $i++) {
      $matches[] = new Wpbb_Match([
        'round_index' => 0,
        'match_index' => $i,
        'team1' => new Wpbb_Team([
          'id' => $i * 2,
          'name' => 'Team ' . $i * 2 + 1,
        ]),
        'team2' => new Wpbb_Team([
          'id' => $i * 2 + 1,
          'name' => 'Team ' . ($i * 2 + 2),
        ]),
      ]);
    }
    return $matches;
  }
}
