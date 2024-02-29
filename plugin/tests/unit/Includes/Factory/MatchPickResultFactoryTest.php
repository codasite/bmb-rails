<?php
use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\MatchPick;
use WStrategies\BMB\Includes\Domain\MatchPickResult;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Factory\MatchPickResultFactory;

class MatchPickResultFactoryTest extends TestCase {
  public function test_create_match_pick_result_correct() {
    $match = new BracketMatch([
      'round_index' => 0,
      'match_index' => 1,
      'team1' => new Team(['name' => 'team1', 'id' => 1]),
      'team2' => new Team(['name' => 'team2', 'id' => 2]),
      'team1_wins' => true,
    ]);
    $pick = new MatchPick([
      'round_index' => 0,
      'match_index' => 1,
      'winning_team_id' => 1,
      'winning_team' => new Team(['name' => 'team1', 'id' => 1]),
    ]);
    $factory = new MatchPickResultFactory();
    $result = $factory->create_match_pick_result($match, $pick);

    $this->assertEquals(0, $result->round_index);
    $this->assertEquals(1, $result->match_index);
    $this->assertEquals('team1', $result->winning_team->name);
    $this->assertEquals('team2', $result->losing_team->name);
    $this->assertTrue($result->correct_picked());
  }

  public function test_create_match_pick_result_incorrect() {
    $match = new BracketMatch([
      'round_index' => 0,
      'match_index' => 1,
      'team1' => new Team(['name' => 'team1', 'id' => 1]),
      'team2' => new Team(['name' => 'team2', 'id' => 2]),
      'team1_wins' => true,
    ]);
    $pick = new MatchPick([
      'round_index' => 0,
      'match_index' => 1,
      'winning_team_id' => 2,
      'winning_team' => new Team(['name' => 'team2', 'id' => 2]),
    ]);
    $factory = new MatchPickResultFactory();
    $result = $factory->create_match_pick_result($match, $pick);

    $this->assertEquals(0, $result->round_index);
    $this->assertEquals(1, $result->match_index);
    $this->assertEquals('team1', $result->winning_team->name);
    $this->assertEquals('team2', $result->losing_team->name);
    $this->assertFalse($result->correct_picked());
  }

  public function test_create_match_pick_result_round_mismatch() {
    $match = new BracketMatch([
      'round_index' => 0,
      'match_index' => 1,
      'team1' => new Team(['name' => 'team1', 'id' => 1]),
      'team2' => new Team(['name' => 'team2', 'id' => 2]),
      'team1_wins' => true,
    ]);
    $pick = new MatchPick([
      'round_index' => 1,
      'match_index' => 1,
      'winning_team_id' => 1,
    ]);
    $factory = new MatchPickResultFactory();
    $this->expectException(ValueError::class);
    $factory->create_match_pick_result($match, $pick);
  }

  public function test_create_match_pick_result_match_mismatch() {
    $match = new BracketMatch([
      'round_index' => 0,
      'match_index' => 1,
      'team1' => new Team(['name' => 'team1', 'id' => 1]),
      'team2' => new Team(['name' => 'team2', 'id' => 2]),
      'team1_wins' => true,
    ]);
    $pick = new MatchPick([
      'round_index' => 0,
      'match_index' => 2,
      'winning_team_id' => 1,
    ]);
    $factory = new MatchPickResultFactory();
    $this->expectException(ValueError::class);
    $factory->create_match_pick_result($match, $pick);
  }

  public function test_create_match_pick_result_no_result() {
    $match = new BracketMatch([
      'round_index' => 0,
      'match_index' => 1,
      'team1' => new Team(['name' => 'team1', 'id' => 1]),
      'team2' => new Team(['name' => 'team2', 'id' => 2]),
    ]);
    $pick = new MatchPick([
      'round_index' => 0,
      'match_index' => 1,
      'winning_team_id' => 1,
    ]);
    $factory = new MatchPickResultFactory();
    $this->expectException(ValueError::class);
    $factory->create_match_pick_result($match, $pick);
  }
}
