<?php
namespace WStrategies\BMB\tests\integration\Includes\service;

use WStrategies\BMB\Includes\Service\BracketLeaderboardService;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class BracketLeaderboardServiceTest extends WPBB_UnitTestCase {
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
  //test sorted by accuracy_score
  public function test_get_plays_sorted_by_accuracy_score() {
    $bracket = $this->create_bracket();
    $play1 = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => true,
      'accuracy_score' => 0.5,
    ]);
    $play2 = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => true,
      'accuracy_score' => 0.75,
    ]);

    $leaderboard = new BracketLeaderboardService($bracket->id);
    $plays = $leaderboard->get_plays();
    $this->assertEquals(2, count($plays));
    $this->assertEquals($play2->id, $plays[0]->id);
    $this->assertEquals($play1->id, $plays[1]->id);
  }

  // test only plays for the bracket are returned
  public function test_get_plays_only_for_bracket() {
    $bracket1 = $this->create_bracket();
    $bracket2 = $this->create_bracket();
    $play1 = $this->create_play([
      'bracket_id' => $bracket1->id,
      'is_tournament_entry' => true,
    ]);
    $play2 = $this->create_play([
      'bracket_id' => $bracket2->id,
      'is_tournament_entry' => true,
    ]);

    $leaderboard = new BracketLeaderboardService($bracket1->id);
    $plays = $leaderboard->get_plays();
    $this->assertEquals(1, count($plays));
    $this->assertEquals($play1->id, $plays[0]->id);
  }

  public function test_get_num_plays_for_bracket() {
    $bracket = $this->create_bracket();
    $play1 = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => true,
    ]);
    $play2 = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => true,
    ]);
    $play3 = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => false,
    ]);

    $leaderboard = new BracketLeaderboardService($bracket->id);
    $this->assertEquals(2, $leaderboard->get_num_plays());
  }

  public function test_get_num_plays_for_user() {
    $user = self::factory()->user->create_and_get();
    $bracket = $this->create_bracket();
    $play1 = $this->create_play([
      'author' => $user->ID,
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => true,
    ]);
    $play2 = $this->create_play([
      'author' => $user->ID,
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => false,
    ]);

    $leaderboard = new BracketLeaderboardService($bracket->id);
    $this->assertEquals(
      1,
      $leaderboard->get_num_plays([
        'author' => $user->ID,
      ])
    );
  }

  public function test_get_plays_for_user() {
    $user = self::factory()->user->create_and_get();
    $bracket = $this->create_bracket();
    $play1 = $this->create_play([
      'author' => $user->ID,
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => true,
    ]);
    $play2 = $this->create_play([
      'author' => $user->ID,
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => false,
    ]);

    $leaderboard = new BracketLeaderboardService();
    $plays = $leaderboard->get_plays([
      'bracket_id' => $bracket->id,
      'author' => $user->ID,
    ]);
    $this->assertEquals(1, count($plays));
    $this->assertEquals($play1->id, $plays[0]->id);
  }
}
