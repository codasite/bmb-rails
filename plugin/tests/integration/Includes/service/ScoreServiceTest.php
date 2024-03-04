<?php

use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Pick;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Service\ScoreService;

class ScoreServiceTest extends WPBB_UnitTestCase {
  public function set_up(): void {
    parent::set_up();
  }

  public function test_round1_correct_pick_is_scored() {
    $bracket = $this->create_bracket([
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

    $this->update_bracket($bracket, [
      'results' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ],
      ],
    ]);

    $update_bracket = $this->get_bracket($bracket->id);

    $play1 = $this->create_play([
      'bracket_id' => $bracket->id,
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);

    $score_service = new ScoreService([
      'tournament_entries_only' => false,
    ]);
    $affected = $score_service->score_bracket_plays($update_bracket);

    $updated = $score_service->play_repo->get($play1->id);

    $this->assertEquals(1, $updated->total_score);
    $this->assertEquals(1, $updated->accuracy_score);
    $this->assertEquals(1, $affected);
  }

  public function test_round1_incorrect_pick_is_not_scored() {
    $bracket = $this->create_bracket([
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

    $this->update_bracket($bracket, [
      'results' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ],
      ],
    ]);

    $update_bracket = $this->get_bracket($bracket->id);

    $play1 = $this->create_play([
      'bracket_id' => $bracket->id,
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team2->id,
        ]),
      ],
    ]);

    $score_service = new ScoreService([
      'tournament_entries_only' => false,
    ]);
    $affected = $score_service->score_bracket_plays($update_bracket);

    $updated = $score_service->play_repo->get($play1->id);

    $this->assertEquals(0, $updated->total_score);
    $this->assertEquals(0, $updated->accuracy_score);
    $this->assertEquals(1, $affected);
  }

  public function test_play_for_different_bracket_is_not_scored() {
    $bracket1 = $this->create_bracket([
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

    $this->update_bracket($bracket1, [
      'results' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket1->matches[0]->team1->id,
        ],
      ],
    ]);

    $bracket2 = $this->create_bracket([
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

    $play = $this->create_play([
      'bracket_id' => $bracket2->id,
      'total_score' => 5,
      'accuracy_score' => 0.5,
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket2->matches[0]->team1->id,
        ]),
      ],
    ]);

    $score_service = new ScoreService([
      'tournament_entries_only' => false,
    ]);
    $affected = $score_service->score_bracket_plays($bracket1);

    $updated = $score_service->play_repo->get($play->id);

    $this->assertEquals(0, $affected);
    $this->assertEquals(5, $updated->total_score);
    $this->assertEquals(0.5, $updated->accuracy_score);
  }

  public function test_score_all_picked() {
    $bracket = $this->create_bracket([
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

    $this->update_bracket($bracket, [
      'results' => $picks,
    ]);

    $update_bracket = $this->get_bracket($bracket->id);

    $play_picks = array_map(function ($pick) {
      return new Pick($pick);
    }, $picks);

    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'picks' => $play_picks,
    ]);

    $score_service = new ScoreService([
      'tournament_entries_only' => false,
    ]);
    $affected = $score_service->score_bracket_plays($update_bracket);

    $updated = $score_service->play_repo->get($play->id);

    $this->assertEquals(12, $updated->total_score);
    $this->assertEquals(1, $updated->accuracy_score);
  }

  public function test_score_third_picked() {
    $bracket = $this->create_bracket([
      'num_teams' => 8,
    ]);

    $point_values = [1, 2, 4, 8, 16, 32];

    $this->update_bracket($bracket, [
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

    $update_bracket = $this->get_bracket($bracket->id);

    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
        new Pick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[1]->team2->id,
        ]),
        new Pick([
          'round_index' => 0,
          'match_index' => 2,
          'winning_team_id' => $bracket->matches[2]->team1->id,
        ]),
        new Pick([
          'round_index' => 0,
          'match_index' => 3,
          'winning_team_id' => $bracket->matches[3]->team2->id,
        ]),
        new Pick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[2]->team2->id,
        ]),
        new Pick([
          'round_index' => 1,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[2]->team1->id,
        ]),
        new Pick([
          'round_index' => 2,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[2]->team1->id,
        ]),
      ],
    ]);

    $score_service = new ScoreService([
      'tournament_entries_only' => false,
    ]);
    $affected = $score_service->score_bracket_plays($update_bracket);

    $updated = $score_service->play_repo->get($play->id);

    $this->assertEquals(4, $updated->total_score);
    $this->assertEquals(0.333333, $updated->accuracy_score);
  }

  public function test_tournament_entries_are_scored() {
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    $bracket = $this->update_bracket($bracket, [
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

    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => true,
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
        new Pick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[1]->team1->id,
        ]),
        new Pick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);

    $score_service = new ScoreService([
      'tournament_entries_only' => true,
    ]);

    $affected = $score_service->score_bracket_plays($bracket);

    $updated = $this->get_play($play->id);
    $this->assertEquals(1, $affected);
    $this->assertNotNull($updated->total_score);
    $this->assertNotNull($updated->accuracy_score);
  }

  public function test_non_tournament_entries_are_not_scored() {
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    $bracket = $this->update_bracket($bracket, [
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

    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => false,
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
        new Pick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[1]->team1->id,
        ]),
        new Pick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);

    $score_service = new ScoreService([
      'tournament_entries_only' => true,
    ]);

    $affected = $score_service->score_bracket_plays($bracket);

    $updated = $score_service->play_repo->get($play->id);
    $this->assertEquals(0, $affected);
    $this->assertNull($updated->total_score);
    $this->assertNull($updated->accuracy_score);
  }

  public function test_set_winners() {
    $bracket = $this->create_bracket();
    $play_winner = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_winner' => false,
      'total_score' => 5,
    ]);
    $play_loser = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_winner' => false,
      'total_score' => 3,
    ]);
    $score_service = new ScoreService([
      'tournament_entries_only' => false,
    ]);

    $score_service->set_winners($bracket);

    $winner = $score_service->play_repo->get($play_winner->id);
    $loser = $score_service->play_repo->get($play_loser->id);

    $this->assertTrue($winner->is_winner);
    $this->assertFalse($loser->is_winner);
  }

  public function test_set_multiple_winners() {
    $bracket = $this->create_bracket();
    $play_winner1 = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_winner' => false,
      'total_score' => 5,
    ]);
    $play_winner2 = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_winner' => false,
      'total_score' => 5,
    ]);
    $play_loser = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_winner' => false,
      'total_score' => 3,
    ]);
    $score_service = new ScoreService([
      'tournament_entries_only' => false,
    ]);

    $score_service->set_winners($bracket);

    $winner1 = $score_service->play_repo->get($play_winner1->id);
    $winner2 = $score_service->play_repo->get($play_winner2->id);
    $loser = $score_service->play_repo->get($play_loser->id);

    $this->assertTrue($winner1->is_winner);
    $this->assertTrue($winner2->is_winner);
    $this->assertFalse($loser->is_winner);
  }

  public function test_winners_set() {
    $bracket = $this->create_bracket();
    $score_service_mock = $this->getMockBuilder(ScoreService::class)
      ->onlyMethods(['set_winners'])
      ->getMock();
    $score_service_mock
      ->expects($this->once())
      ->method('set_winners')
      ->with($bracket);

    $score_service_mock->score_bracket_plays($bracket, true);
  }
  public function test_winners_not_set() {
    $bracket = $this->create_bracket();
    $score_service_mock = $this->getMockBuilder(ScoreService::class)
      ->onlyMethods(['set_winners'])
      ->getMock();
    $score_service_mock->expects($this->never())->method('set_winners');

    $score_service_mock->score_bracket_plays($bracket);
  }
}
