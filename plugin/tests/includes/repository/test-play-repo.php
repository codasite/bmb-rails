<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-bracket-play.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-bracket.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-repo.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-play-repo.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-repo.php';

class PlayRepoTest extends WPBB_UnitTestCase {
  private $play_repo;

  public function set_up() {
    parent::set_up();

    $this->play_repo = new Wpbb_BracketPlayRepo();
  }

  public function test_add() {
    $bracket = self::factory()->bracket->create_and_get([
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
        new Wpbb_Match([
          'round_index' => 0,
          'match_index' => 1,
          'team1' => new Wpbb_Team([
            'name' => 'Team 3',
          ]),
          'team2' => new Wpbb_Team([
            'name' => 'Team 4',
          ]),
        ]),
      ],
    ]);

    $play = new Wpbb_BracketPlay([
      'bracket_id' => $bracket->id,
      'author' => 1,
      'total_score' => 5,
      'accuracy_score' => 0.3,
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
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);

    $play = $this->play_repo->add($play);

    $this->assertNotNull($play->id);
    $this->assertEquals($bracket->id, $play->bracket_id);
    $this->assertEquals(1, $play->author);

    $new_picks = $play->picks;

    $this->assertEquals(3, count($new_picks));
    $this->assertEquals(0, $new_picks[0]->round_index);
    $this->assertEquals(0, $new_picks[0]->match_index);
    $this->assertEquals(
      $bracket->matches[0]->team1->id,
      $new_picks[0]->winning_team_id
    );
    $this->assertEquals(0, $new_picks[1]->round_index);
    $this->assertEquals(1, $new_picks[1]->match_index);
    $this->assertEquals(
      $bracket->matches[1]->team2->id,
      $new_picks[1]->winning_team_id
    );
    $this->assertEquals(1, $new_picks[2]->round_index);
    $this->assertEquals(0, $new_picks[2]->match_index);
    $this->assertEquals(
      $bracket->matches[0]->team1->id,
      $new_picks[2]->winning_team_id
    );
    $this->assertEquals(5, $play->total_score);
    $this->assertEquals(0.3, $play->accuracy_score);
  }

  public function test_get() {
    $bracket = self::factory()->bracket->create_and_get([
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
        new Wpbb_Match([
          'round_index' => 0,
          'match_index' => 1,
          'team1' => new Wpbb_Team([
            'name' => 'Team 3',
          ]),
          'team2' => new Wpbb_Team([
            'name' => 'Team 4',
          ]),
        ]),
      ],
    ]);
    $play = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
      'author' => 1,
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
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);

    $get_play = $this->play_repo->get($play->id);

    $this->assertEquals($play->id, $get_play->id);
    $this->assertEquals($play->bracket_id, $get_play->bracket_id);
    $this->assertEquals($play->author, $get_play->author);

    $new_picks = $get_play->picks;

    $this->assertEquals(3, count($new_picks));
    $this->assertEquals(0, $new_picks[0]->round_index);
    $this->assertEquals(0, $new_picks[0]->match_index);
    $this->assertEquals(
      $bracket->matches[0]->team1->id,
      $new_picks[0]->winning_team_id
    );
    $this->assertEquals(0, $new_picks[1]->round_index);
    $this->assertEquals(1, $new_picks[1]->match_index);
    $this->assertEquals(
      $bracket->matches[1]->team2->id,
      $new_picks[1]->winning_team_id
    );
    $this->assertEquals(1, $new_picks[2]->round_index);
    $this->assertEquals(0, $new_picks[2]->match_index);
    $this->assertEquals(
      $bracket->matches[0]->team1->id,
      $new_picks[2]->winning_team_id
    );
  }

  public function test_get_all() {
    $bracket = self::factory()->bracket->create_and_get([
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

    $play1 = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
      'author' => 1,
      'picks' => [
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);

    $play2 = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
      'author' => 1,
      'picks' => [
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team2->id,
        ]),
      ],
    ]);

    $plays = $this->play_repo->get_all();

    $this->assertEquals(2, count($plays));
    $this->assertEquals($play1->id, $plays[0]->id);
    $this->assertEquals($play2->id, $plays[1]->id);
  }
  public function test_get_user_picks_for_result() {
    $bracket = self::factory()->bracket->create_object([
      'num_teams' => 4,
    ]);

    $user = self::factory()->user->create_and_get();

    $play = self::factory()->play->create_object([
      'bracket_id' => $bracket->id,
      'author' => $user->ID,
      'picks' => [
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);

    $result = new Wpbb_MatchPick([
      'round_index' => 0,
      'match_index' => 0,
      'winning_team_id' => $bracket->matches[0]->team1->id,
    ]);

    $user_picks = $this->play_repo->get_user_picks_for_result(
      $bracket->id,
      $result
    );

    $this->assertEquals(count($user_picks), 1);
    $user_pick = $user_picks[0];
    // $this->assertEquals($play->id, $user_pick['play_id']);
    $this->assertEquals($user->ID, $user_pick['user_id']);
    $this->assertEquals($play->picks[0]->id, $user_pick['pick_id']);

    $pick = $this->play_repo->get_pick($user_picks[0]['pick_id']);
    $this->assertEquals($result->round_index, $pick->round_index);
    $this->assertEquals($result->match_index, $pick->match_index);
    $this->assertEquals($result->winning_team_id, $pick->winning_team_id);
  }

  public function test_get_multiple_user_picks_for_result() {
    $bracket = self::factory()->bracket->create_object([
      'num_teams' => 4,
    ]);

    $user1 = self::factory()->user->create_and_get();
    $user2 = self::factory()->user->create_and_get();

    $play1 = self::factory()->play->create_object([
      'bracket_id' => $bracket->id,
      'author' => $user1->ID,
      'picks' => [
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[1]->team1->id,
        ]),
        new Wpbb_MatchPick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);

    $play2 = self::factory()->play->create_object([
      'bracket_id' => $bracket->id,
      'author' => $user2->ID,
      'picks' => [
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[1]->team1->id,
        ]),
        new Wpbb_MatchPick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team2->id,
        ]),
      ],
    ]);

    $result = new Wpbb_MatchPick([
      'round_index' => 1,
      'match_index' => 0,
      'winning_team_id' => $bracket->matches[0]->team1->id,
    ]);

    $user_picks = $this->play_repo->get_user_picks_for_result(
      $bracket->id,
      $result
    );

    $this->assertEquals(count($user_picks), 2);

    $user_pick1 = $user_picks[0];
    $this->assertEquals($user1->ID, $user_pick1['user_id']);
    $this->assertEquals($play1->picks[2]->id, $user_pick1['pick_id']);
    $pick1 = $this->play_repo->get_pick($user_pick1['pick_id']);
    $this->assertEquals($result->round_index, $pick1->round_index);
    $this->assertEquals($result->match_index, $pick1->match_index);
    $this->assertEquals($result->winning_team_id, $pick1->winning_team_id);

    $user_pick2 = $user_picks[1];
    $this->assertEquals($user2->ID, $user_pick2['user_id']);
    $this->assertEquals($play2->picks[2]->id, $user_pick2['pick_id']);
    $pick2 = $this->play_repo->get_pick($user_pick2['pick_id']);
    $this->assertEquals($result->round_index, $pick2->round_index);
    $this->assertEquals($result->match_index, $pick2->match_index);
    $this->assertNotEquals($result->winning_team_id, $pick2->winning_team_id);
    $this->assertEquals(
      $bracket->matches[0]->team2->id,
      $pick2->winning_team_id
    );
  }
  public function test_update_is_printed() {
    $bracket = self::factory()->bracket->create_and_get([
      'num_teams' => 4,
    ]);
    $play = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
      'is_printed' => false,
      'picks' => [
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);

    $updated = $this->play_repo->update($play->id, [
      'is_printed' => true,
    ]);

    $this->assertTrue($updated->is_printed);
    $this->assertEquals($play->id, $updated->id);
    $this->assertEquals($play->bracket_id, $updated->bracket_id);
    $this->assertEquals($play->author, $updated->author);
    $this->assertEquals($play->title, $updated->title);
    $this->assertEquals($play->total_score, $updated->total_score);
    $this->assertEquals($play->accuracy_score, $updated->accuracy_score);
    $this->assertEquals($play->busted_id, $updated->busted_id);
  }

  public function test_get_plays_for_bracket_id() {
    $bracket = self::factory()->bracket->create_and_get([
      'num_teams' => 4,
    ]);
    $play1 = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
      'picks' => [
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);
    $play2 = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
      'picks' => [
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team2->id,
        ]),
      ],
    ]);

    $bracket2 = self::factory()->bracket->create_and_get([
      'num_teams' => 4,
    ]);

    $play3 = self::factory()->play->create_and_get([
      'bracket_id' => $bracket2->id,
      'picks' => [
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team2->id,
        ]),
      ],
    ]);

    $plays = $this->play_repo->get_all([
      'bracket_id' => $bracket->id,
    ]);

    $this->assertEquals(2, count($plays));
    $this->assertEquals($play1->id, $plays[0]->id);
    $this->assertEquals($play2->id, $plays[1]->id);
  }

  public function test_sort_plays_by_total_score() {
    $bracket = self::factory()->bracket->create_and_get([
      'num_teams' => 4,
    ]);
    $play1 = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
      'total_score' => 5,
    ]);
    $play2 = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
      'total_score' => 10,
    ]);
    $play3 = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
      'total_score' => 15,
    ]);

    $plays = $this->play_repo->get_all([
      'bracket_id' => $bracket->id,
      'orderby' => 'total_score',
      'order' => 'DESC',
    ]);

    $this->assertEquals(3, count($plays));
    $this->assertEquals($play3->id, $plays[0]->id);
    $this->assertEquals($play2->id, $plays[1]->id);
    $this->assertEquals($play1->id, $plays[2]->id);
  }

  public function test_sort_plays_by_accuracy_score() {
    $bracket = self::factory()->bracket->create_and_get([
      'num_teams' => 4,
    ]);
    $play1 = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
      'accuracy_score' => 0.5,
    ]);
    $play2 = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
      'accuracy_score' => 0.75,
    ]);
    $play3 = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
      'accuracy_score' => 0.25,
    ]);

    $plays = $this->play_repo->get_all([
      'bracket_id' => $bracket->id,
      'orderby' => 'accuracy_score',
      'order' => 'DESC',
    ]);

    $this->assertEquals(3, count($plays));
    $this->assertEquals($play2->id, $plays[0]->id);
    $this->assertEquals($play1->id, $plays[1]->id);
    $this->assertEquals($play3->id, $plays[2]->id);
  }

  public function test_query_printed() {
    $bracket = self::factory()->bracket->create_and_get([
      'num_teams' => 4,
    ]);
    $play1 = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
      'is_printed' => true,
    ]);
    $play2 = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
      'is_printed' => false,
    ]);

    $plays = $this->play_repo->get_all([
      'bracket_id' => $bracket->id,
      'is_printed' => true,
    ]);

    $this->assertEquals(1, count($plays));
    $this->assertEquals($play1->id, $plays[0]->id);
  }

  public function test_query_printed_ignore_late_plays() {
    $bracket = self::factory()->bracket->create_and_get([
      'num_teams' => 4,
    ]);

    $bracket_results_date = '2020-03-01 12:00:00';

    $ontime_date = '2020-03-01 11:59:59';
    $late_date = '2020-03-01 12:00:01';

    $play1 = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
      'is_printed' => true,
    ]);
    $play2 = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
      'is_printed' => true,
    ]);
    $play3 = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
      'is_printed' => false,
    ]);
    $play4 = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
      'is_printed' => false,
    ]);
    wp_update_post([
      'ID' => $play1->id,
      'post_date_gmt' => $ontime_date,
    ]);
    wp_update_post([
      'ID' => $play2->id,
      'post_date_gmt' => $ontime_date,
    ]);
    wp_update_post([
      'ID' => $play3->id,
      'post_date_gmt' => $late_date,
    ]);
    wp_update_post([
      'ID' => $play4->id,
      'post_date_gmt' => $late_date,
    ]);

    // query for all plays with post_date_gmt before bracket_results_date
    $plays = $this->play_repo->get_all([
      'bracket_id' => $bracket->id,
      'is_printed' => true,
      'date_query' => [
        [
          'column' => 'post_date_gmt',
          'before' => $bracket_results_date,
        ],
      ],
    ]);

    $this->assertEquals(2, count($plays));
    $this->assertEquals($play1->id, $plays[0]->id);
    $this->assertEquals($play2->id, $plays[1]->id);
  }

  public function test_get_with_busted_play() {
    $bracket = self::factory()->bracket->create_and_get([
      'num_teams' => 4,
    ]);
    $busted = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
    ]);
    $buster = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
      'busted_id' => $busted->id,
    ]);

    $play = $this->play_repo->get($buster->id);

    $this->assertEquals($busted->id, $play->busted_id);
    $this->assertEquals($busted->id, $play->busted_play->id);
  }
}
