<?php

namespace WStrategies\BMB\tests\unit\Includes\Service;
use Spatie\Snapshots\MatchesSnapshots;
use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Pick;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Service\BracketMatchService;

class BracketMatchServiceTest extends TestCase {
  use MatchesSnapshots;
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

  public function test_matches_from_picks() {
    $base_matches = [
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 0,
        'team1' => new Team(['name' => 'Team 1', 'id' => 1]),
        'team2' => new Team(['name' => 'Team 2', 'id' => 2]),
      ]),
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 1,
        'team1' => new Team(['name' => 'Team 3', 'id' => 3]),
        'team2' => new Team(['name' => 'Team 4', 'id' => 4]),
      ]),
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 2,
        'team1' => new Team(['name' => 'Team 5', 'id' => 5]),
        'team2' => new Team(['name' => 'Team 6', 'id' => 6]),
      ]),
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 3,
        'team1' => new Team(['name' => 'Team 7', 'id' => 7]),
        'team2' => new Team(['name' => 'Team 8', 'id' => 8]),
      ]),
    ];

    $picks = [
      new Pick([
        'round_index' => 0,
        'match_index' => 0,
        'winning_team_id' => 1,
        'winning_team' => new Team(['name' => 'Team 1', 'id' => 1]),
      ]),
      new Pick([
        'round_index' => 0,
        'match_index' => 1,
        'winning_team_id' => 3,
        'winning_team' => new Team(['name' => 'Team 3', 'id' => 3]),
      ]),
      new Pick([
        'round_index' => 0,
        'match_index' => 2,
        'winning_team_id' => 6,
        'winning_team' => new Team(['name' => 'Team 6', 'id' => 6]),
      ]),
      new Pick([
        'round_index' => 0,
        'match_index' => 3,
        'winning_team_id' => 7,
        'winning_team' => new Team(['name' => 'Team 7', 'id' => 7]),
      ]),
      new Pick([
        'round_index' => 1,
        'match_index' => 0,
        'winning_team_id' => 1,
        'winning_team' => new Team(['name' => 'Team 1', 'id' => 1]),
      ]),
      new Pick([
        'round_index' => 1,
        'match_index' => 1,
        'winning_team_id' => 7,
        'winning_team' => new Team(['name' => 'Team 7', 'id' => 7]),
      ]),
      new Pick([
        'round_index' => 2,
        'match_index' => 0,
        'winning_team_id' => 7,
        'winning_team' => new Team(['name' => 'Team 7', 'id' => 7]),
      ]),
    ];

    $service = new BracketMatchService();
    $picked_matches = $service->matches_2d_from_picks($base_matches, $picks);
    $this->assertMatchesJsonSnapshot($picked_matches);
  }

  public function test_matches_from_picks_6_team() {
    $base_matches = [
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 0,
        'team1' => new Team(['name' => 'Team 1', 'id' => 1]),
        'team2' => new Team(['name' => 'Team 2', 'id' => 2]),
      ]),
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 2,
        'team1' => new Team(['name' => 'Team 3', 'id' => 3]),
        'team2' => new Team(['name' => 'Team 4', 'id' => 4]),
      ]),
      new BracketMatch([
        'round_index' => 1,
        'match_index' => 0,
        'team1' => null,
        'team2' => new Team(['name' => 'Team 5', 'id' => 5]),
      ]),
      new BracketMatch([
        'round_index' => 1,
        'match_index' => 1,
        'team1' => null,
        'team2' => new Team(['name' => 'Team 6', 'id' => 6]),
      ]),
    ];

    $picks = [
      new Pick([
        'round_index' => 0,
        'match_index' => 0,
        'winning_team_id' => 1,
        'winning_team' => new Team(['name' => 'Team 1', 'id' => 1]),
      ]),
      new Pick([
        'round_index' => 0,
        'match_index' => 2,
        'winning_team_id' => 4,
        'winning_team' => new Team(['name' => 'Team 4', 'id' => 4]),
      ]),
      new Pick([
        'round_index' => 1,
        'match_index' => 0,
        'winning_team_id' => 1,
        'winning_team' => new Team(['name' => 'Team 1', 'id' => 1]),
      ]),
      new Pick([
        'round_index' => 1,
        'match_index' => 1,
        'winning_team_id' => 6,
        'winning_team' => new Team(['name' => 'Team 6', 'id' => 6]),
      ]),
      new Pick([
        'round_index' => 2,
        'match_index' => 0,
        'winning_team_id' => 1,
        'winning_team' => new Team(['name' => 'Team 1', 'id' => 1]),
      ]),
    ];

    $service = new BracketMatchService();
    $picked_matches = $service->matches_2d_from_picks($base_matches, $picks);
    $this->assertMatchesJsonSnapshot($picked_matches);
  }
  public function test_matches_from_picks_6_team_partial() {
    $base_matches = [
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 0,
        'team1' => new Team(['name' => 'Team 1', 'id' => 1]),
        'team2' => new Team(['name' => 'Team 2', 'id' => 2]),
      ]),
      new BracketMatch([
        'round_index' => 0,
        'match_index' => 2,
        'team1' => new Team(['name' => 'Team 3', 'id' => 3]),
        'team2' => new Team(['name' => 'Team 4', 'id' => 4]),
      ]),
      new BracketMatch([
        'round_index' => 1,
        'match_index' => 0,
        'team1' => null,
        'team2' => new Team(['name' => 'Team 5', 'id' => 5]),
      ]),
      new BracketMatch([
        'round_index' => 1,
        'match_index' => 1,
        'team1' => null,
        'team2' => new Team(['name' => 'Team 6', 'id' => 6]),
      ]),
    ];

    $picks = [
      new Pick([
        'round_index' => 0,
        'match_index' => 0,
        'winning_team_id' => 1,
        'winning_team' => new Team(['name' => 'Team 1', 'id' => 1]),
      ]),
      new Pick([
        'round_index' => 0,
        'match_index' => 2,
        'winning_team_id' => 4,
        'winning_team' => new Team(['name' => 'Team 4', 'id' => 4]),
      ]),
      new Pick([
        'round_index' => 1,
        'match_index' => 0,
        'winning_team_id' => 1,
        'winning_team' => new Team(['name' => 'Team 1', 'id' => 1]),
      ]),
    ];

    $service = new BracketMatchService();
    $picked_matches = $service->matches_2d_from_picks($base_matches, $picks);
    $this->assertMatchesJsonSnapshot($picked_matches);
  }

  public function test_should_return_all_items_when_all_nodes_are_present() {
    $matches_2d = [
      [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team(['name' => 'Team 1']),
          'team2' => new Team(['name' => 'Team 2']),
          'team1_wins' => true,
        ]),
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 1,
          'team1' => new Team(['name' => 'Team 3']),
          'team2' => new Team(['name' => 'Team 4']),
          'team1_wins' => true,
        ]),
      ],
      [
        new BracketMatch([
          'round_index' => 1,
          'match_index' => 0,
          'team1' => new Team(['name' => 'Team 5']),
          'team2' => new Team(['name' => 'Team 6']),
          'team1_wins' => true,
        ]),
      ],
    ];

    $results = [
      new Pick([
        'round_index' => 0,
        'match_index' => 0,
        'winning_team_id' => 1,
        'winning_team' => new Team(['name' => 'Team 1']),
      ]),
      new Pick([
        'round_index' => 0,
        'match_index' => 1,
        'winning_team_id' => 3,
        'winning_team' => new Team(['name' => 'Team 3']),
      ]),
      new Pick([
        'round_index' => 1,
        'match_index' => 0,
        'winning_team_id' => 5,
        'winning_team' => new Team(['name' => 'Team 5']),
      ]),
    ];

    $service = new BracketMatchService();
    $filtered = $service->filter_2d_array($matches_2d, $results);
    $this->assertCount(2, $filtered);
    $this->assertCount(2, $filtered[0]);
    $this->assertCount(1, $filtered[1]);
    $this->assertMatchesJsonSnapshot($filtered);
  }

  public function test_should_not_return_items_for_missing_nodes() {
    $matches_2d = [
      [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team(['name' => 'Team 1']),
          'team2' => new Team(['name' => 'Team 2']),
          'team1_wins' => true,
        ]),
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 1,
          'team1' => new Team(['name' => 'Team 3']),
          'team2' => new Team(['name' => 'Team 4']),
          'team1_wins' => true,
        ]),
      ],
      [
        new BracketMatch([
          'round_index' => 1,
          'match_index' => 0,
          'team1' => new Team(['name' => 'Team 5']),
          'team2' => new Team(['name' => 'Team 6']),
          'team1_wins' => true,
        ]),
      ],
    ];

    $results = [
      new Pick([
        'round_index' => 0,
        'match_index' => 0,
        'winning_team_id' => 1,
        'winning_team' => new Team(['name' => 'Team 1']),
      ]),
      new Pick([
        'round_index' => 1,
        'match_index' => 0,
        'winning_team_id' => 5,
        'winning_team' => new Team(['name' => 'Team 5']),
      ]),
    ];

    $service = new BracketMatchService();
    $filtered = $service->filter_2d_array($matches_2d, $results);
    $this->assertCount(2, $filtered);
    $this->assertCount(1, $filtered[0]);
    $this->assertCount(1, $filtered[1]);
    $this->assertMatchesJsonSnapshot($filtered);
  }

  public function test_should_return_no_items_when_no_nodes_are_present() {
    $matches_2d = [
      [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team(['name' => 'Team 1']),
          'team2' => new Team(['name' => 'Team 2']),
          'team1_wins' => true,
        ]),
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 1,
          'team1' => new Team(['name' => 'Team 3']),
          'team2' => new Team(['name' => 'Team 4']),
          'team1_wins' => true,
        ]),
      ],
      [
        new BracketMatch([
          'round_index' => 1,
          'match_index' => 0,
          'team1' => new Team(['name' => 'Team 5']),
          'team2' => new Team(['name' => 'Team 6']),
          'team1_wins' => true,
        ]),
      ],
    ];

    $results = [];

    $service = new BracketMatchService();
    $filtered = $service->filter_2d_array($matches_2d, $results);
    $this->assertCount(0, $filtered);
  }
}
