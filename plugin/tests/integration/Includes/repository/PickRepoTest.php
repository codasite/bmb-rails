<?php
namespace WStrategies\BMB\tests\integration\Includes\repository;

use Spatie\Snapshots\MatchesSnapshots;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Pick;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\PickRepo;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class PickRepoTest extends WPBB_UnitTestCase {
  use MatchesSnapshots;
  private PlayRepo $play_repo;
  private PickRepo $pick_repo;
  private BracketRepo $bracket_repo;

  public function set_up(): void {
    parent::set_up();

    $this->play_repo = new PlayRepo();
    $this->bracket_repo = new BracketRepo();
    $this->pick_repo = $this->play_repo->pick_repo;
  }

  public function test_should_return_correct_most_popular_picks() {
    $bracket = $this->create_bracket([
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
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 1,
          'team1' => new Team([
            'name' => 'Team 3',
          ]),
          'team2' => new Team([
            'name' => 'Team 4',
          ]),
        ]),
      ],
    ]);
    $team1_id = $bracket->matches[0]->team1->id;
    $team2_id = $bracket->matches[0]->team2->id;
    $team3_id = $bracket->matches[1]->team1->id;
    $team4_id = $bracket->matches[1]->team2->id;

    $play = new Play([
      'bracket_id' => $bracket->id,
      'author' => 1,
      'is_tournament_entry' => true,
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $team2_id,
        ]),
        new Pick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $team3_id,
        ]),
        new Pick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $team3_id,
        ]),
      ],
    ]);
    $play = $this->play_repo->add($play);
    $this->play_repo->add(
      new Play([
        'bracket_id' => $bracket->id,
        'author' => 1,
        'is_tournament_entry' => true,
        'picks' => [
          new Pick([
            'round_index' => 0,
            'match_index' => 0,
            'winning_team_id' => $team1_id,
          ]),
          new Pick([
            'round_index' => 0,
            'match_index' => 1,
            'winning_team_id' => $team3_id,
          ]),
          new Pick([
            'round_index' => 1,
            'match_index' => 0,
            'winning_team_id' => $team1_id,
          ]),
        ],
      ])
    );
    $this->play_repo->add(
      new Play([
        'bracket_id' => $bracket->id,
        'author' => 1,
        'is_tournament_entry' => true,
        'picks' => [
          new Pick([
            'round_index' => 0,
            'match_index' => 0,
            'winning_team_id' => $team1_id,
          ]),
          new Pick([
            'round_index' => 0,
            'match_index' => 1,
            'winning_team_id' => $team3_id,
          ]),
          new Pick([
            'round_index' => 1,
            'match_index' => 0,
            'winning_team_id' => $team1_id,
          ]),
        ],
      ])
    );

    $bracket = $this->bracket_repo->get($bracket, false, false, true);
    $most_popular_picks = $bracket->most_popular_picks;
    $this->assertEquals(3, count($most_popular_picks));
    $this->assertEquals(0, $most_popular_picks[0]->round_index);
    $this->assertEquals(0, $most_popular_picks[0]->match_index);
    $this->assertEquals($team1_id, $most_popular_picks[0]->winning_team->id);
    $this->assertEquals(0.6667, $most_popular_picks[0]->popularity);

    $this->assertEquals(0, $most_popular_picks[1]->round_index);
    $this->assertEquals(1, $most_popular_picks[1]->match_index);
    $this->assertEquals($team3_id, $most_popular_picks[1]->winning_team->id);
    $this->assertEquals(1, $most_popular_picks[1]->popularity);

    $this->assertEquals(1, $most_popular_picks[2]->round_index);
    $this->assertEquals(0, $most_popular_picks[2]->match_index);
    $this->assertEquals($team1_id, $most_popular_picks[2]->winning_team_id);
    $this->assertEquals(0.6667, $most_popular_picks[2]->popularity);
  }

  public function test_should_return_num_picks_for_round() {
    $bracket = $this->create_bracket([
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
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 1,
          'team1' => new Team([
            'name' => 'Team 3',
          ]),
          'team2' => new Team([
            'name' => 'Team 4',
          ]),
        ]),
      ],
    ]);
    $team1_id = $bracket->matches[0]->team1->id;
    $team2_id = $bracket->matches[0]->team2->id;
    $team3_id = $bracket->matches[1]->team1->id;
    $team4_id = $bracket->matches[1]->team2->id;

    $play = new Play([
      'bracket_id' => $bracket->id,
      'author' => 1,
      'is_tournament_entry' => true,
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $team2_id,
        ]),
        new Pick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $team3_id,
        ]),
      ],
    ]);
    $play = $this->play_repo->add($play);
    $this->assertEquals(
      2,
      $this->pick_repo->get_num_picks_for_round($bracket->id, 0)
    );
    $this->assertEquals(
      0,
      $this->pick_repo->get_num_picks_for_round($bracket->id, 1)
    );
  }
  public function test_should_return_correct_most_popular_picks_for_round() {
    $bracket = $this->create_bracket([
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
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 1,
          'team1' => new Team([
            'name' => 'Team 3',
          ]),
          'team2' => new Team([
            'name' => 'Team 4',
          ]),
        ]),
      ],
    ]);
    $team1_id = $bracket->matches[0]->team1->id;
    $team2_id = $bracket->matches[0]->team2->id;
    $team3_id = $bracket->matches[1]->team1->id;
    $team4_id = $bracket->matches[1]->team2->id;

    $play = new Play([
      'bracket_id' => $bracket->id,
      'author' => 1,
      'is_tournament_entry' => true,
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $team2_id,
        ]),
        new Pick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $team3_id,
        ]),
        new Pick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $team3_id,
        ]),
      ],
    ]);
    $play = $this->play_repo->add($play);
    $this->play_repo->add(
      new Play([
        'bracket_id' => $bracket->id,
        'author' => 1,
        'is_tournament_entry' => true,
        'picks' => [
          new Pick([
            'round_index' => 0,
            'match_index' => 0,
            'winning_team_id' => $team1_id,
          ]),
          new Pick([
            'round_index' => 0,
            'match_index' => 1,
            'winning_team_id' => $team3_id,
          ]),
          new Pick([
            'round_index' => 1,
            'match_index' => 0,
            'winning_team_id' => $team1_id,
          ]),
        ],
      ])
    );
    $this->play_repo->add(
      new Play([
        'bracket_id' => $bracket->id,
        'author' => 1,
        'is_tournament_entry' => true,
        'picks' => [
          new Pick([
            'round_index' => 0,
            'match_index' => 0,
            'winning_team_id' => $team1_id,
          ]),
          new Pick([
            'round_index' => 0,
            'match_index' => 1,
            'winning_team_id' => $team3_id,
          ]),
          new Pick([
            'round_index' => 1,
            'match_index' => 0,
            'winning_team_id' => $team1_id,
          ]),
        ],
      ])
    );

    $most_popular_picks = $this->pick_repo->get_most_popular_picks(
      $bracket->id,
      ['round_index' => 1]
    );
    $this->assertEquals(1, count($most_popular_picks));
    $this->assertEquals(1, $most_popular_picks[0]->round_index);
    $this->assertEquals(0, $most_popular_picks[0]->match_index);
    $this->assertEquals($team1_id, $most_popular_picks[0]->winning_team_id);
    $this->assertEquals(0.6667, $most_popular_picks[0]->popularity);
  }
}
