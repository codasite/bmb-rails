<?php
use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Includes\Domain\MatchPickResult;
use WStrategies\BMB\Includes\Domain\Team;

class MatchPickResultTest extends TestCase {
  public function test_constructor() {
    $pick_result = new MatchPickResult([
      'round_index' => 1,
      'match_index' => 2,
      'winning_team' => new Team(['name' => 'team1']),
      'losing_team' => new Team(['name' => 'team2']),
      'correct_picked' => true,
    ]);

    $this->assertEquals(1, $pick_result->round_index);
    $this->assertEquals(2, $pick_result->match_index);
    $this->assertTrue($pick_result->correct_picked);
    $this->assertEquals('team1', $pick_result->winning_team->name);
    $this->assertEquals('team2', $pick_result->losing_team->name);
  }
}
