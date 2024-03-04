<?php

use PHPUnit\Framework\TestCase;
use WStrategies\BMB\Includes\Domain\Team;

class TeamTest extends TestCase {
  public function test_constructor() {
    $team = new Team([
      'name' => 'team1',
      'id' => 1,
    ]);

    $this->assertEquals('team1', $team->name);
    $this->assertEquals(1, $team->id);
  }

  public function get_should_return_mapping_of_team_ids_to_team_objects() {
    $teams = [
      new Team([
        'name' => 'team1',
        'id' => 1,
      ]),
      new Team([
        'name' => 'team2',
        'id' => 2,
      ]),
    ];

    $team_id_map = Team::get_team_id_map($teams);

    $this->assertCount(2, $team_id_map);
    $this->assertEquals($team_id_map[1]->name, 'team1');
    $this->assertEquals($team_id_map[2]->name, 'team2');
  }

  public function test_equals_should_return_true_when_team_ids_match() {
    $team = new Team([
      'name' => 'team1',
      'id' => 1,
    ]);

    $this->assertTrue($team->equals(1));
    $this->assertTrue(
      $team->equals(
        new Team([
          'name' => 'team1',
          'id' => 1,
        ])
      )
    );
  }

  public function test_equals_should_return_false_when_team_ids_do_not_match() {
    $team = new Team([
      'name' => 'team1',
      'id' => 1,
    ]);

    $this->assertFalse($team->equals(2));
    $this->assertFalse(
      $team->equals(
        new Team([
          'name' => 'team2',
          'id' => 2,
        ])
      )
    );
  }
}
