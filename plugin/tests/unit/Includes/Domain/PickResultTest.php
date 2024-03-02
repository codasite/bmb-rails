<?php
namespace WStrategies\BMB\tests\unit\Includes\Domain;
use ValueError;
use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Fakes\PickResultFakeFactory;
use WStrategies\BMB\Includes\Domain\Pick;
use WStrategies\BMB\Includes\Domain\PickResult;
use WStrategies\BMB\Includes\Domain\Team;

class PickResultTest extends TestCase {
  public function test_constructor() {
    $pick_result = new PickResult(
      new BracketMatch([
        'round_index' => 1,
        'match_index' => 2,
        'team1_wins' => 1,
        'team2_wins' => 0,
        'team1' => new Team(['name' => 'team1', 'id' => 1]),
        'team2' => new Team(['name' => 'team2', 'id' => 2]),
      ]),
      new Pick([
        'round_index' => 1,
        'match_index' => 2,
        'winning_team_id' => 1,
        'winning_team' => new Team(['name' => 'team1', 'id' => 1]),
      ])
    );

    $this->assertEquals('team1', $pick_result->match->get_winning_team()->name);
    $this->assertEquals('team2', $pick_result->match->get_losing_team()->name);
    $this->assertEquals('team1', $pick_result->get_picked_team()->name);
    $this->assertEquals('team1', $pick_result->get_team1()->name);
    $this->assertEquals('team2', $pick_result->get_team2()->name);
  }

  public function test_returns_true_when_picked_team_won() {
    $pick_result = new PickResult(
      new BracketMatch([
        'round_index' => 1,
        'match_index' => 2,
        'team1_wins' => 1,
        'team2_wins' => 0,
        'team1' => new Team(['name' => 'team1', 'id' => 1]),
        'team2' => new Team(['name' => 'team2', 'id' => 2]),
      ]),
      new Pick([
        'round_index' => 1,
        'match_index' => 2,
        'winning_team_id' => 1,
        'winning_team' => new Team(['name' => 'team1', 'id' => 1]),
      ])
    );
    $this->assertTrue($pick_result->picked_team_won());
  }

  public function test_returns_false_when_picked_team_did_not_win() {
    $pick_result = new PickResult(
      new BracketMatch([
        'round_index' => 1,
        'match_index' => 2,
        'team1_wins' => 1,
        'team2_wins' => 0,
        'team1' => new Team(['name' => 'team1', 'id' => 1]),
        'team2' => new Team(['name' => 'team2', 'id' => 2]),
      ]),
      new Pick([
        'round_index' => 1,
        'match_index' => 2,
        'winning_team_id' => 2,
        'winning_team' => new Team(['name' => 'team2', 'id' => 2]),
      ])
    );
    $this->assertFalse($pick_result->picked_team_won());
  }

  public function test_returns_true_when_picked_team_played() {
    $pick_result = new PickResult(
      new BracketMatch([
        'round_index' => 1,
        'match_index' => 2,
        'team1_wins' => 1,
        'team2_wins' => 0,
        'team1' => new Team(['name' => 'team1', 'id' => 1]),
        'team2' => new Team(['name' => 'team2', 'id' => 2]),
      ]),
      new Pick([
        'round_index' => 1,
        'match_index' => 2,
        'winning_team_id' => 1,
        'winning_team' => new Team(['name' => 'team1', 'id' => 1]),
      ])
    );
    $this->assertTrue($pick_result->picked_team_played());
  }

  public function test_returns_false_when_picked_team_did_not_play() {
    $pick_result = new PickResult(
      new BracketMatch([
        'round_index' => 1,
        'match_index' => 2,
        'team1_wins' => 1,
        'team2_wins' => 0,
        'team1' => new Team(['name' => 'team1', 'id' => 1]),
        'team2' => new Team(['name' => 'team2', 'id' => 2]),
      ]),
      new Pick([
        'round_index' => 1,
        'match_index' => 2,
        'winning_team_id' => 3,
        'winning_team' => new Team(['name' => 'team3', 'id' => 3]),
      ])
    );
    $this->assertFalse($pick_result->picked_team_played());
  }

  public function test_create_match_pick_result_round_mismatch() {
    $match = new BracketMatch([
      'round_index' => 0,
      'match_index' => 1,
      'team1' => new Team(['name' => 'team1', 'id' => 1]),
      'team2' => new Team(['name' => 'team2', 'id' => 2]),
      'team1_wins' => true,
    ]);
    $pick = new Pick([
      'round_index' => 1,
      'match_index' => 1,
      'winning_team_id' => 1,
    ]);
    $this->expectException(ValueError::class);
    new PickResult($match, $pick);
  }

  public function test_create_match_pick_result_match_mismatch() {
    $match = new BracketMatch([
      'round_index' => 0,
      'match_index' => 1,
      'team1' => new Team(['name' => 'team1', 'id' => 1]),
      'team2' => new Team(['name' => 'team2', 'id' => 2]),
      'team1_wins' => true,
    ]);
    $pick = new Pick([
      'round_index' => 0,
      'match_index' => 2,
      'winning_team_id' => 1,
    ]);
    $this->expectException(ValueError::class);
    new PickResult($match, $pick);
  }

  public function test_create_match_pick_result_no_result() {
    $match = new BracketMatch([
      'round_index' => 0,
      'match_index' => 1,
      'team1' => new Team(['name' => 'team1', 'id' => 1]),
      'team2' => new Team(['name' => 'team2', 'id' => 2]),
    ]);
    $pick = new Pick([
      'round_index' => 0,
      'match_index' => 1,
      'winning_team_id' => 1,
    ]);
    $this->expectException(ValueError::class);
    new PickResult($match, $pick);
  }
}
