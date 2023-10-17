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

    $bracket = self::factory()->bracket->create_object([
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
      'results' => [
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => 1,
        ]),
      ],
    ]);

    $plays = [
      self::factory()->play->create_object([
        'bracket_id' => $bracket->id,
        'picks' => [
          new Wpbb_MatchPick([
            'round_index' => 0,
            'match_index' => 0,
            'winning_team_id' => 1,
          ]),
        ],
      ]),
      self::factory()->play->create_object([
        'bracket_id' => $bracket->id,
        'picks' => [
          new Wpbb_MatchPick([
            'round_index' => 0,
            'match_index' => 0,
            'winning_team_id' => 2,
          ]),
        ],
      ]),
    ];

    $this->service->score_bracket_plays($bracket);

    foreach ($plays as $play) {
      $play = self::factory()->play->get_object_by_id($play->id);
      $this->assertNotNull($play->total_score);
      echo $play->total_score;
    }
  }
}
