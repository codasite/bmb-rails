<?php

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Repository\BracketRepo;

/**
 * Class WPBB_UnitTest_Factory_For_Play
 *
 * This class is used to create template objects for unit testing
 */
class WPBB_UnitTest_Factory_For_Bracket extends WP_UnitTest_Factory_For_Thing {
  private $bracket_repo;

  function __construct($factory = null) {
    parent::__construct($factory);
    $this->bracket_repo = new BracketRepo();

    $this->default_generation_definitions = [
      'title' => new WP_UnitTest_Generator_Sequence('Bracket %s'),
      'author' => 1,
      'num_teams' => 4,
    ];
  }

  function create_object($args) {
    if (isset($args['num_teams']) && !isset($args['matches'])) {
      $args['matches'] = $this->generateMatches($args['num_teams']);
    }
    $bracket = new Bracket($args);
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
      $matches[] = new BracketMatch([
        'round_index' => 0,
        'match_index' => $i,
        'team1' => new Team([
          'name' => 'Team ' . $i * 2 + 1,
        ]),
        'team2' => new Team([
          'name' => 'Team ' . ($i * 2 + 2),
        ]),
      ]);
    }
    return $matches;
  }
}
