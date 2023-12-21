<?php

use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\MatchPick;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Service\ScoreService;

class Test_ScoreService extends WPBB_UnitTestCase {
  public function set_up() {
    parent::set_up();
  }

  public function test_round1_correct_pick_is_scored() {
    $bracket = self::factory()->bracket->create_object([
      'num_teams' => 2,
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'id' => 1,
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'id' => 2,
            'name' => 'Team 2',
          ]),
        ]),
      ],
    ]);

    self::factory()->bracket->update_object($bracket, [
      'results' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ],
      ],
    ]);

    $update_bracket = self::factory()->bracket->get_object_by_id($bracket->id);

    $play1 = self::factory()->play->create_object([
      'bracket_id' => $bracket->id,
      'picks' => [
        new MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);

    $score_service = new ScoreService([
      'ignore_late_plays' => false,
    ]);
    $affected = $score_service->score_bracket_plays($update_bracket);

    $updated = $score_service->play_repo->get($play1->id);

    $this->assertEquals(1, $updated->total_score);
    $this->assertEquals(1, $updated->accuracy_score);
    $this->assertEquals(1, $affected);
  }

  public function test_round1_incorrect_pick_is_not_scored() {
    $bracket = self::factory()->bracket->create_object([
      'num_teams' => 2,
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'name' => 'Team 2',
          ]),
        ]),
      ],
    ]);

    self::factory()->bracket->update_object($bracket, [
      'results' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ],
      ],
    ]);

    $update_bracket = self::factory()->bracket->get_object_by_id($bracket->id);

    $play1 = self::factory()->play->create_object([
      'bracket_id' => $bracket->id,
      'picks' => [
        new MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team2->id,
        ]),
      ],
    ]);

    $score_service = new ScoreService([
      'ignore_late_plays' => false,
    ]);
    $affected = $score_service->score_bracket_plays($update_bracket);

    $updated = $score_service->play_repo->get($play1->id);

    $this->assertEquals(0, $updated->total_score);
    $this->assertEquals(0, $updated->accuracy_score);
    $this->assertEquals(1, $affected);
  }

  public function test_play_for_different_bracket_is_not_scored() {
    $bracket1 = self::factory()->bracket->create_object([
      'num_teams' => 2,
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'name' => 'Team 2',
          ]),
        ]),
      ],
    ]);

    self::factory()->bracket->update_object($bracket1, [
      'results' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket1->matches[0]->team1->id,
        ],
      ],
    ]);

    $bracket2 = self::factory()->bracket->create_object([
      'num_teams' => 2,
      'matches' => [
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Team([
            'name' => 'Team 2',
          ]),
        ]),
      ],
    ]);

    $play = self::factory()->play->create_object([
      'bracket_id' => $bracket2->id,
      'total_score' => 5,
      'accuracy_score' => 0.5,
      'picks' => [
        new MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket2->matches[0]->team1->id,
        ]),
      ],
    ]);

    $score_service = new ScoreService([
      'ignore_late_plays' => false,
    ]);
    $affected = $score_service->score_bracket_plays($bracket1);

    $updated = $score_service->play_repo->get($play->id);

    $this->assertEquals(0, $affected);
    $this->assertEquals(5, $updated->total_score);
    $this->assertEquals(0.5, $updated->accuracy_score);
  }

  public function test_score_all_picked() {
    $bracket = self::factory()->bracket->create_object([
      'num_teams' => 8,
    ]);

    $point_values = [1, 2, 4, 8, 16, 32];

    $picks = [
      [
        'round_index' => 0,
        'match_index' => 0,
        'winning_team_id' => $bracket->matches[0]->team1->id,
      ],
      [
        'round_index' => 0,
        'match_index' => 1,
        'winning_team_id' => $bracket->matches[1]->team1->id,
      ],
      [
        'round_index' => 0,
        'match_index' => 2,
        'winning_team_id' => $bracket->matches[2]->team1->id,
      ],
      [
        'round_index' => 0,
        'match_index' => 3,
        'winning_team_id' => $bracket->matches[3]->team1->id,
      ],
      [
        'round_index' => 1,
        'match_index' => 0,
        'winning_team_id' => $bracket->matches[0]->team1->id,
      ],
      [
        'round_index' => 1,
        'match_index' => 1,
        'winning_team_id' => $bracket->matches[2]->team1->id,
      ],
      [
        'round_index' => 2,
        'match_index' => 0,
        'winning_team_id' => $bracket->matches[0]->team1->id,
      ],
    ];

    self::factory()->bracket->update_object($bracket, [
      'results' => $picks,
    ]);

    $update_bracket = self::factory()->bracket->get_object_by_id($bracket->id);

    $play_picks = array_map(function ($pick) {
      return new MatchPick($pick);
    }, $picks);

    $play = self::factory()->play->create_object([
      'bracket_id' => $bracket->id,
      'picks' => $play_picks,
    ]);

    $score_service = new ScoreService([
      'ignore_late_plays' => false,
    ]);
    $affected = $score_service->score_bracket_plays($update_bracket);

    $updated = $score_service->play_repo->get($play->id);

    $this->assertEquals(12, $updated->total_score);
    $this->assertEquals(1, $updated->accuracy_score);
  }

  public function test_score_third_picked() {
    $bracket = self::factory()->bracket->create_object([
      'num_teams' => 8,
    ]);

    $point_values = [1, 2, 4, 8, 16, 32];

    self::factory()->bracket->update_object($bracket, [
      'results' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ],
        [
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[1]->team1->id,
        ],
        [
          'round_index' => 0,
          'match_index' => 2,
          'winning_team_id' => $bracket->matches[2]->team1->id,
        ],
        [
          'round_index' => 0,
          'match_index' => 3,
          'winning_team_id' => $bracket->matches[3]->team1->id,
        ],
        [
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ],
        [
          'round_index' => 1,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[2]->team1->id,
        ],
        [
          'round_index' => 2,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ],
      ],
    ]);

    $update_bracket = self::factory()->bracket->get_object_by_id($bracket->id);

    $play = self::factory()->play->create_object([
      'bracket_id' => $bracket->id,
      'picks' => [
        new MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
        new MatchPick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[1]->team2->id,
        ]),
        new MatchPick([
          'round_index' => 0,
          'match_index' => 2,
          'winning_team_id' => $bracket->matches[2]->team1->id,
        ]),
        new MatchPick([
          'round_index' => 0,
          'match_index' => 3,
          'winning_team_id' => $bracket->matches[3]->team2->id,
        ]),
        new MatchPick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[2]->team2->id,
        ]),
        new MatchPick([
          'round_index' => 1,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[2]->team1->id,
        ]),
        new MatchPick([
          'round_index' => 2,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[2]->team1->id,
        ]),
      ],
    ]);

    $score_service = new ScoreService([
      'ignore_late_plays' => false,
    ]);
    $affected = $score_service->score_bracket_plays($update_bracket);

    $updated = $score_service->play_repo->get($play->id);

    $this->assertEquals(4, $updated->total_score);
    $this->assertEquals(0.333333, $updated->accuracy_score);
  }

  public function test_play_created_before_results_update_is_scored() {
    $bracket = self::factory()->bracket->create_object([
      'num_teams' => 4,
    ]);
    $bracket = self::factory()->bracket->update_object($bracket, [
      'results' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ],
        [
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[1]->team1->id,
        ],
      ],
    ]);

    $play = self::factory()->play->create_object([
      'bracket_id' => $bracket->id,
      'picks' => [
        new MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
        new MatchPick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[1]->team1->id,
        ]),
        new MatchPick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);
    $play_time = $play->published_date;
    $bracket_time = $play_time->modify('+1 second');

    $bracket = self::factory()->bracket->update_object($bracket, [
      'results_first_updated_at' => $bracket_time->format('Y-m-d H:i:s'),
    ]);

    $score_service = new ScoreService([
      'ignore_late_plays' => true,
    ]);

    $affected = $score_service->score_bracket_plays($bracket);

    $updated = self::factory()->play->get_object_by_id($play->id);
    $this->assertEquals(1, $affected);
    $this->assertNotNull($updated->total_score);
    $this->assertNotNull($updated->accuracy_score);
  }

  public function test_play_created_after_results_updated_not_scored() {
    $bracket = self::factory()->bracket->create_object([
      'num_teams' => 4,
    ]);
    $bracket = self::factory()->bracket->update_object($bracket, [
      'results' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ],
        [
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[1]->team1->id,
        ],
      ],
    ]);

    $play = self::factory()->play->create_object([
      'bracket_id' => $bracket->id,
      'picks' => [
        new MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
        new MatchPick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[1]->team1->id,
        ]),
        new MatchPick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);

    $play_time = $play->published_date;
    $bracket_time = $play_time->modify('-1 second');

    $bracket = self::factory()->bracket->update_object($bracket, [
      'results_first_updated_at' => $bracket_time->format('Y-m-d H:i:s'),
    ]);

    $score_service = new ScoreService([
      'ignore_late_plays' => true,
    ]);

    $affected = $score_service->score_bracket_plays($bracket);

    $updated = $score_service->play_repo->get($play->id);
    $this->assertEquals(0, $affected);
    $this->assertNull($updated->total_score);
    $this->assertNull($updated->accuracy_score);
  }

  public function test_set_winners() {
    $bracket = self::factory()->bracket->create_and_get();
    $play_winner = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
      'is_winner' => false,
      'total_score' => 5,
    ]);
    $play_loser = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
      'is_winner' => false,
      'total_score' => 3,
    ]);
    $score_service = new ScoreService([
      'ignore_late_plays' => false,
    ]);

    $score_service->set_winners($bracket);

    $winner = $score_service->play_repo->get($play_winner->id);
    $loser = $score_service->play_repo->get($play_loser->id);

    $this->assertTrue($winner->is_winner);
    $this->assertFalse($loser->is_winner);
  }
}
