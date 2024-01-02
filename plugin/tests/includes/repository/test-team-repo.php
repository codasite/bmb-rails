<?php

use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\BracketTeamRepo;

class TeamRepoTest extends WPBB_UnitTestCase {
  private $team_repo;
  private $bracket_repo;

  public function set_up() {
    parent::set_up();

    $this->team_repo = new BracketTeamRepo();
    $this->bracket_repo = new BracketRepo();
  }

  public function test_add() {
    $bracket = $this->create_bracket([
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'name' => 'Team 2',
          ]),
        ]),
      ],
    ]);

    $dirty_name = "Rosie O'Donnell";
    $clean_name = "Rosie O\'Donnell";
    $team = new Team([
      'name' => $dirty_name,
    ]);

    $bracket_data = $this->bracket_repo->get_custom_table_data($bracket->id);
    $team = $this->team_repo->add($bracket_data['id'], $team);

    $this->assertNotNull($team->id);
    $this->assertEquals($dirty_name, $team->name);
  }

  public function test_update() {
    $bracket = $this->create_bracket([
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'name' => 'Team 2',
          ]),
        ]),
      ],
    ]);

    $team1 = $bracket->matches[0]->team1;
    $team1->name = 'Team 1 Updated';
    $this->team_repo->update($team1->id, $team1);

    $team1 = $this->team_repo->get($team1->id);

    $this->assertEquals('Team 1 Updated', $team1->name);
  }
}
