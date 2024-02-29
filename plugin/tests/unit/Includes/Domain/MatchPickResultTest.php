<?php
use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Includes\Domain\MatchPickResult;
use WStrategies\BMB\Includes\Domain\Team;

class MatchPickResultTest extends TestCase {
  public function test_constructor() {
    $pick_result = new MatchPickResult([
      'round_index' => 1,
      'match_index' => 2,
      'winning_team' => new Team(['name' => 'team1', 'id' => 1]),
      'losing_team' => new Team(['name' => 'team2', 'id' => 2]),
      'picked_team' => new Team(['name' => 'team1', 'id' => 1]),
    ]);

    $this->assertEquals(1, $pick_result->round_index);
    $this->assertEquals(2, $pick_result->match_index);
    $this->assertEquals('team1', $pick_result->winning_team->name);
    $this->assertEquals('team2', $pick_result->losing_team->name);
  }

  public function test_correct_picked_should_return_true_when_picked_team_is_winning_team() {
    $pick_result = new MatchPickResult([
      'round_index' => 1,
      'match_index' => 2,
      'winning_team' => new Team(['name' => 'team1', 'id' => 1]),
      'losing_team' => new Team(['name' => 'team2', 'id' => 2]),
      'picked_team' => new Team(['name' => 'team1', 'id' => 1]),
    ]);

    $this->assertTrue($pick_result->correct_picked());
  }

  public function test_correct_picked_should_return_false_when_picked_team_is_not_winning_team() {
    $pick_result = new MatchPickResult([
      'round_index' => 1,
      'match_index' => 2,
      'winning_team' => new Team(['name' => 'team1', 'id' => 1]),
      'losing_team' => new Team(['name' => 'team2', 'id' => 2]),
      'picked_team' => new Team(['name' => 'team2', 'id' => 2]),
    ]);

    $this->assertFalse($pick_result->correct_picked());
  }
}
