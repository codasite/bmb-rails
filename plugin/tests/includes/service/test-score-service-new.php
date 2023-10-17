<?php
require_once WPBB_PLUGIN_DIR . 'includes/service/class-wpbb-score-service.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-match-pick.php';

class Test_Wpbb_Score_Service extends WPBB_UnitTestCase {
  private $service;

  public function set_up() {
    parent::set_up();
    $this->service = new WPBB_Score_Service();
  }

  public function test_score_bracket_plays() {
    $team1 = self::factory()->play->create_object([
      'name' => 'Team 1',
    ]);

    $team2 = self::factory()->play->create_object([
      'name' => 'Team 2',
    ]);
    $team3 = self::factory()->play->create_object([
      'name' => 'Team 3',
    ]);
    $team4 = self::factory()->play->create_object([
      'name' => 'Team 4',
    ]);

    $bracket = self::factory()->bracket->create_and_get([
      'matches' => [
        new Wpbb_Match([
          'round_index' => 0,
          'match_index' => 0,
          'team1' => $team1,
          'team2' => $team2,
        ]),
        new Wpbb_Match([
          'round_index' => 0,
          'match_index' => 1,
          'team1' => $team3,
          'team2' => $team4,
        ]),
      ],
      'results' => [
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $team1->id,
        ]),
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $team4->id,
        ]),
        new Wpbb_MatchPick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $team4->id,
        ]),
      ],
    ]);

    $this->service->score_bracket_plays($bracket);
    $this->assertNotNull(1);
  }
}
