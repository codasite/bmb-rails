<?php

use WStrategies\BMB\Includes\Service\BracketLeaderboardService;

class LeaderboardServiceTest extends WPBB_UnitTestCase {
  public function test_get_bracket() {
    $bracket = $this->create_bracket();
    $leaderboard = new BracketLeaderboardService($bracket->id);
    $this->assertEquals($bracket->id, $leaderboard->get_bracket()->id);
  }

  public function test_get_plays() {
    $bracket = $this->create_bracket();
    $play1 = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => true,
    ]);
    $play2 = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => true,
    ]);

    $leaderboard = new BracketLeaderboardService($bracket->id);
    $plays = $leaderboard->get_plays();
    $this->assertEquals(2, count($plays));
    $this->assertEquals($play1->id, $plays[0]->id);
    $this->assertEquals($play2->id, $plays[1]->id);
  }

  public function test_get_plays_only_tournament_entrys() {
    $bracket = $this->create_bracket();
    $play1 = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => true,
    ]);
    $play2 = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => false,
    ]);

    $leaderboard = new BracketLeaderboardService($bracket->id);
    $plays = $leaderboard->get_plays();
    $this->assertEquals(1, count($plays));
    $this->assertEquals($play1->id, $plays[0]->id);
  }
}
