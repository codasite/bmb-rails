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
    $team1 = $bracket->matches[0]->team1->id;
    $team2 = $bracket->matches[0]->team2->id;
    $team3 = $bracket->matches[1]->team1->id;
    $team4 = $bracket->matches[1]->team2->id;

    $play = new Play([
      'bracket_id' => $bracket->id,
      'author' => 1,
      'is_tournament_entry' => true,
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $team2,
        ]),
        new Pick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $team3,
        ]),
        new Pick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $team3,
        ]),
      ],
    ]);
    $play = $this->play_repo->add($play);
    $this->play_repo->add(new Play([
      'bracket_id' => $bracket->id,
      'author' => 1,
      'is_tournament_entry' => true,
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $team1,
        ]),
        new Pick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $team3,
        ]),
        new Pick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $team1,
        ]),
      ]
    ]));
    $this->play_repo->add(new Play([
      'bracket_id' => $bracket->id,
      'author' => 1,
      'is_tournament_entry' => true,
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $team1,
        ]),
        new Pick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $team3,
        ]),
        new Pick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $team1,
        ]),
      ]
    ]));

    $bracket = $this->bracket_repo->get($bracket, false, false, true);
    $most_popular_picks = $bracket->most_popular_picks;
    $this->assertMatchesJsonSnapshot($most_popular_picks);
  }
}
