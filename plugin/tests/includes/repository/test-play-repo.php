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
  public function test_get_user_pick_for_result() {
    $bracket = self::factory()->bracket->create_object([
      'num_teams' => 4,
    ]);

    $user = $this->factory->user->create_and_get();

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

    $user_picks = $this->play_repo->get_user_pick_for_result(
      $bracket->id,
      $result
    );

    $this->assertEquals(count($user_picks), 1);
    $user_pick = $user_picks[0];
    // $this->assertEquals($play->id, $user_pick['play_id']);
    $this->assertEquals($user->ID, $user_pick['user']->ID);
    $this->assertEquals($play->picks[0]->id, $user_pick['pick']->id);
    $this->assertEquals($result->round_index, $user_pick['pick']->round_index);
    $this->assertEquals($result->match_index, $user_pick['pick']->match_index);
    $this->assertEquals(
      $result->winning_team_id,
      $user_pick['pick']->winning_team_id
    );
  }

  public function test_get_multiple_user_picks_for_result() {
    $bracket = self::factory()->bracket->create_object([
      'num_teams' => 4,
    ]);

    $user1 = $this->factory->user->create_and_get();
    $user2 = $this->factory->user->create_and_get();

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

    $user_picks = $this->play_repo->get_user_pick_for_result(
      $bracket->id,
      $result
    );

    $this->assertEquals(count($user_picks), 2);

    $user_pick1 = $user_picks[0];
    $this->assertEquals($user1->ID, $user_pick1['user']->ID);
    $this->assertEquals($play1->picks[2]->id, $user_pick1['pick']->id);
    $this->assertEquals($result->round_index, $user_pick1['pick']->round_index);
    $this->assertEquals($result->match_index, $user_pick1['pick']->match_index);
    $this->assertEquals(
      $result->winning_team_id,
      $user_pick1['pick']->winning_team_id
    );

    $user_pick2 = $user_picks[1];
    $this->assertEquals($user2->ID, $user_pick2['user']->ID);
    $this->assertEquals($play2->picks[2]->id, $user_pick2['pick']->id);
    $this->assertEquals($result->round_index, $user_pick2['pick']->round_index);
    $this->assertEquals($result->match_index, $user_pick2['pick']->match_index);
    $this->assertNotEquals(
      $result->winning_team_id,
      $user_pick2['pick']->winning_team_id
    );
    $this->assertEquals(
      $bracket->matches[0]->team2->id,
      $user_pick2['pick']->winning_team_id
    );
  }
}
