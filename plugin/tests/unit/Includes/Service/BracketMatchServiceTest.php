<?php

use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Service\BracketMatchService;

class BracketMatchServiceTest extends TestCase {
  public function test_matches_to_2d_array_8_team() {
    $matches = [
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 0,
        'team1' => new Team(['name' => 'Team 1']),
        'team2' => new Team(['name' => 'Team 2']),
      ]),
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 1,
        'team1' => new Team(['name' => 'Team 3']),
        'team2' => new Team(['name' => 'Team 4']),
      ]),
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 2,
        'team1' => new Team(['name' => 'Team 5']),
        'team2' => new Team(['name' => 'Team 6']),
      ]),
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 3,
        'team1' => new Team(['name' => 'Team 7']),
        'team2' => new Team(['name' => 'Team 8']),
      ]),
    ];

    $arr = (new BracketMatchService())->match_node_2d($matches);

    $this->assertEquals(1, count($arr));
    $this->assertEquals(4, count($arr[0]));
  }
  public function test_matches_to_2d_array_12_team_split() {
    $matches = [
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 0,
        'team1' => new Team(['name' => 'Team 1']),
        'team2' => new Team(['name' => 'Team 2']),
      ]),
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 3,
        'team1' => new Team(['name' => 'Team 3']),
        'team2' => new Team(['name' => 'Team 4']),
      ]),
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 4,
        'team1' => new Team(['name' => 'Team 5']),
        'team2' => new Team(['name' => 'Team 6']),
      ]),
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 7,
        'team1' => new Team(['name' => 'Team 7']),
        'team2' => new Team(['name' => 'Team 8']),
      ]),
      new BracketMatch([
        'round_index' => 1,
        'match_index' => 0,
        'team1' => null,
        'team2' => new Team(['name' => 'Team 9']),
      ]),
      new BracketMatch([
        'round_index' => 1,
        'match_index' => 1,
        'team1' => new Team(['name' => 'Team 10']),
        'team2' => null,
      ]),
      new BracketMatch([
        'round_index' => 1,
        'match_index' => 2,
        'team1' => null,
        'team2' => new Team(['name' => 'Team 11']),
      ]),
      new BracketMatch([
        'round_index' => 1,
        'match_index' => 3,
        'team1' => new Team(['name' => 'Team 12']),
        'team2' => null,
      ]),
    ];

    $arr = (new BracketMatchService())->match_node_2d($matches);

    $this->assertEquals(2, count($arr));
    $this->assertEquals(4, count($arr[0]));
    $this->assertEquals(4, count($arr[1]));
  }
}
