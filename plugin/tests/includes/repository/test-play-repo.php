<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-bracket-play.php';
require_once WPBB_PLUGIN_DIR .
  'includes/domain/class-wpbb-bracket-tournament.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-tournament-repo.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-bracket-play-repo.php';

class PlayRepoTest extends WPBB_UnitTestCase {
  private $play_repo;

  public function set_up() {
    parent::set_up();

    $this->play_repo = new Wpbb_BracketPlayRepo();
  }

  public function test_add() {
    $template = self::factory()->template->create_and_get([
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
    $tournament = self::factory()->tournament->create_and_get([
      'bracket_template_id' => $template->id,
    ]);

    $play = new Wpbb_BracketPlay([
      'tournament_id' => $tournament->id,
      'author' => 1,
      'picks' => [
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $template->matches[0]->team1->id,
        ]),
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $template->matches[1]->team2->id,
        ]),
        new Wpbb_MatchPick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $template->matches[0]->team1->id,
        ]),
      ],
    ]);

    $play = $this->play_repo->add($play);

    $this->assertNotNull($play->id);
    $this->assertEquals($tournament->id, $play->tournament_id);
    $this->assertEquals(1, $play->author);

    $new_picks = $play->picks;

    $this->assertEquals(3, count($new_picks));
    $this->assertEquals(0, $new_picks[0]->round_index);
    $this->assertEquals(0, $new_picks[0]->match_index);
    $this->assertEquals(
      $template->matches[0]->team1->id,
      $new_picks[0]->winning_team_id
    );
    $this->assertEquals(0, $new_picks[1]->round_index);
    $this->assertEquals(1, $new_picks[1]->match_index);
    $this->assertEquals(
      $template->matches[1]->team2->id,
      $new_picks[1]->winning_team_id
    );
    $this->assertEquals(1, $new_picks[2]->round_index);
    $this->assertEquals(0, $new_picks[2]->match_index);
    $this->assertEquals(
      $template->matches[0]->team1->id,
      $new_picks[2]->winning_team_id
    );
  }

  public function test_get() {
    $template = self::factory()->template->create_and_get([
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
    $tournament = self::factory()->tournament->create_and_get([
      'bracket_template_id' => $template->id,
    ]);
    $play = self::factory()->play->create_and_get([
      'tournament_id' => $tournament->id,
      'author' => 1,
      'picks' => [
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $template->matches[0]->team1->id,
        ]),
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $template->matches[1]->team2->id,
        ]),
        new Wpbb_MatchPick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $template->matches[0]->team1->id,
        ]),
      ],
    ]);

    $get_play = $this->play_repo->get($play->id);

    $this->assertEquals($play->id, $get_play->id);
    $this->assertEquals($play->tournament_id, $get_play->tournament_id);
    $this->assertEquals($play->author, $get_play->author);

    $new_picks = $get_play->picks;

    $this->assertEquals(3, count($new_picks));
    $this->assertEquals(0, $new_picks[0]->round_index);
    $this->assertEquals(0, $new_picks[0]->match_index);
    $this->assertEquals(
      $template->matches[0]->team1->id,
      $new_picks[0]->winning_team_id
    );
    $this->assertEquals(0, $new_picks[1]->round_index);
    $this->assertEquals(1, $new_picks[1]->match_index);
    $this->assertEquals(
      $template->matches[1]->team2->id,
      $new_picks[1]->winning_team_id
    );
    $this->assertEquals(1, $new_picks[2]->round_index);
    $this->assertEquals(0, $new_picks[2]->match_index);
    $this->assertEquals(
      $template->matches[0]->team1->id,
      $new_picks[2]->winning_team_id
    );
  }

  public function test_get_all() {
    $template = self::factory()->template->create_and_get([
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
      'tournament_id' => self::factory()->tournament->create_and_get([
        'bracket_template_id' => $template->id,
      ])->id,
      'author' => 1,
      'picks' => [
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $template->matches[0]->team1->id,
        ]),
      ],
    ]);

    $play2 = self::factory()->play->create_and_get([
      'tournament_id' => self::factory()->tournament->create_and_get([
        'bracket_template_id' => $template->id,
      ])->id,
      'author' => 1,
      'picks' => [
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $template->matches[0]->team2->id,
        ]),
      ],
    ]);

    $plays = $this->play_repo->get_all();

    $this->assertEquals(2, count($plays));
    $this->assertEquals($play1->id, $plays[0]->id);
    $this->assertEquals($play2->id, $plays[1]->id);
  }
}
