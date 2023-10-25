<?php
require_once WPBB_PLUGIN_DIR . 'includes/service/class-wpbb-score-service.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-match-pick.php';

class Test_Wpbb_ScoreService extends WPBB_UnitTestCase {
  public function set_up() {
    parent::set_up();
  }

  public function test_round1_correct_pick_is_scored() {
    $bracket = self::factory()->bracket->create_object([
      'num_teams' => 2,
      'matches' => [
        new Wpbb_Match([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Wpbb_Team([
            'id' => 1,
            'name' => 'Team 1',
          ]),
          'team2' => new Wpbb_Team([
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
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);

    $score_service = new Wpbb_ScoreService([
      'only_score_printed_plays' => false,
      'check_timestamp' => false,
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
        new Wpbb_Match([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Wpbb_Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Wpbb_Team([
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
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team2->id,
        ]),
      ],
    ]);

    $score_service = new Wpbb_ScoreService([
      'only_score_printed_plays' => false,
      'check_timestamp' => false,
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
        new Wpbb_Match([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Wpbb_Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Wpbb_Team([
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
        new Wpbb_Match([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Wpbb_Team([
            'name' => 'Team 1',
          ]),
          'team2' => new Wpbb_Team([
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
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket2->matches[0]->team1->id,
        ]),
      ],
    ]);

    $score_service = new Wpbb_ScoreService([
      'only_score_printed_plays' => false,
      'check_timestamp' => false,
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
      return new Wpbb_MatchPick($pick);
    }, $picks);

    $play = self::factory()->play->create_object([
      'bracket_id' => $bracket->id,
      'picks' => $play_picks,
    ]);

    $score_service = new Wpbb_ScoreService([
      'only_score_printed_plays' => false,
      'check_timestamp' => false,
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
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[1]->team2->id,
        ]),
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 2,
          'winning_team_id' => $bracket->matches[2]->team1->id,
        ]),
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 3,
          'winning_team_id' => $bracket->matches[3]->team2->id,
        ]),
        new Wpbb_MatchPick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[2]->team2->id,
        ]),
        new Wpbb_MatchPick([
          'round_index' => 1,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[2]->team1->id,
        ]),
        new Wpbb_MatchPick([
          'round_index' => 2,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[2]->team1->id,
        ]),
      ],
    ]);

    $score_service = new Wpbb_ScoreService([
      'only_score_printed_plays' => false,
      'check_timestamp' => false,
    ]);
    $affected = $score_service->score_bracket_plays($update_bracket);

    $updated = $score_service->play_repo->get($play->id);

    $this->assertEquals(4, $updated->total_score);
    $this->assertEquals(0.333333, $updated->accuracy_score);
  }

  public function test_only_score_printed_plays() {
    $bracket = self::factory()->bracket->create_object([
      'num_teams' => 2,
      'matches' => [
        new Wpbb_Match([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Wpbb_Team([
            'id' => 1,
            'name' => 'Team 1',
          ]),
          'team2' => new Wpbb_Team([
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
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
      'is_printed' => true,
    ]);

    $play2 = self::factory()->play->create_object([
      'bracket_id' => $bracket->id,
      'picks' => [
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);

    $score_service = new Wpbb_ScoreService([
      'only_score_printed_plays' => true,
      'check_timestamp' => false,
    ]);
    $affected = $score_service->score_bracket_plays($update_bracket);

    $scored = $score_service->play_repo->get($play1->id);

    $this->assertEquals(1, $scored->total_score);
    $this->assertEquals(1, $scored->accuracy_score);
    $this->assertEquals(1, $affected);

    $unscored = $score_service->play_repo->get($play2->id);
    $this->assertEquals(null, $unscored->total_score);
    $this->assertEquals(null, $unscored->accuracy_score);
  }
}
