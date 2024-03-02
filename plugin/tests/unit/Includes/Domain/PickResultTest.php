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
  }

  public function test_correct_picked_should_return_true_when_picked_team_is_winning_team() {
    $pick_result = PickResultFakeFactory::get_correct_pick_result();
    $this->assertTrue($pick_result->correct_picked());
  }

  public function test_correct_picked_should_return_false_when_picked_team_is_not_winning_team() {
    $pick_result = PickResultFakeFactory::get_incorrect_pick_result();
    $this->assertFalse($pick_result->correct_picked());
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
