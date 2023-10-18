<?php
require_once WPBB_PLUGIN_DIR . 'includes/service/class-wpbb-score-service.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-match-pick.php';

class Test_Wpbb_Score_Service extends WPBB_UnitTestCase {
  public function set_up() {
    parent::set_up();
  }

  public function test_round1_correct_pick_is_scored() {
    $bracket = self::factory()->bracket->create_object([
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
      'results' => [
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => 1,
        ]),
      ],
    ]);

    $play1 = self::factory()->play->create_object([
      'bracket_id' => $bracket->id,
      'picks' => [
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => 1,
        ]),
      ],
    ]);

    echo 'score test';
    $score_service = new Wpbb_Score_Service();
    $score_service->score_bracket_plays($bracket);

    $updated = $score_service->play_repo->get($play1->id);
    $bracket_data = $score_service->bracket_repo->get_bracket_data(
      $bracket->id
    );
    global $wpdb;
    // echo 'bracket_data: ' . print_r($bracket_data, true);
    $play_data = $score_service->play_repo->get_play_data($play1->id);
    // echo 'play_data: ' . print_r($play_data, true);
    $results_sql = "SELECT * FROM {$score_service->bracket_repo->results_table()}";
    $results = $wpdb->get_results($results_sql, ARRAY_A);
    $play_picks_sql = "SELECT * FROM {$score_service->play_repo->picks_table()}";
    $play_picks = $wpdb->get_results($play_picks_sql, ARRAY_A);
    // echo 'play_picks: ' . print_r($play_picks, true);
    // echo 'results: ' . print_r($results, true);

    $bracket_id = $bracket_data['id'];

    // $score_sql = "
    //     SELECT p1.bracket_play_id,
    //     COALESCE(SUM(CASE WHEN p1.round_index = 0 THEN 1 ELSE 0 END), 0) AS round0correct, COALESCE(SUM(CASE WHEN p1.round_index = 1 THEN 1 ELSE 0 END), 0) AS round1correct
    // FROM wptests_bracket_builder_match_picks p1
    // JOIN wptests_bracket_builder_bracket_results p2 ON p1.round_index = p2.round_index
    //                                                         AND p1.match_index = p2.match_index
    //                                                         AND p1.winning_team_id = p2.winning_team_id
    //                                                         AND p2.bracket_id = $bracket_id
    // GROUP BY p1.bracket_play_id";
    //     $score_sql = "
    //     SELECT p1.bracket_play_id
    // FROM wptests_bracket_builder_match_picks p1
    // JOIN wptests_bracket_builder_bracket_results p2 ON p1.round_index = p2.round_index
    //                                                         AND p1.match_index = p2.match_index
    //                                                         AND p1.winning_team_id = p2.winning_team_id
    //                                                         AND p2.bracket_id = $bracket_id;";
    $score_sql = "SELECT * FROM {$score_service->play_repo->plays_table()}";

    $score = $wpdb->get_results($score_sql, ARRAY_A);
    echo 'score: ' . print_r($score, true);

    $this->assertEquals(1, $updated->total_score);
  }
}
