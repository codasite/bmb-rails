<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-team.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-team-repo.php';

class TeamRepoTest extends WPBB_UnitTestCase {
  private $team_repo;
  private $bracket_repo;

  public function set_up() {
    parent::set_up();

    $this->team_repo = new Wpbb_BracketTeamRepo();
    $this->bracket_repo = new Wpbb_BracketRepo();
  }

  public function test_add() {
    $bracket = self::factory()->bracket->create_and_get([
      'matches' => [
        new Wpbb_Match([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Wpbb_Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Wpbb_Team([
            'name' => 'Team 2',
          ]),
        ]),
      ],
    ]);

    $dirty_name = "Rosie O'Donnell";
    $clean_name = "Rosie O\'Donnell";
    $team = new Wpbb_Team([
      'name' => $dirty_name,
    ]);

    $bracket_data = $this->bracket_repo->get_bracket_data($bracket->id);
    $team = $this->team_repo->add($bracket_data['id'], $team);

    $this->assertNotNull($team->id);
    $this->assertEquals($dirty_name, $team->name);
  }
}
