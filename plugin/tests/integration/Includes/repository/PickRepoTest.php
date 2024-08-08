<?php
namespace WStrategies\BMB\tests\integration\Includes\repository;

use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Pick;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Repository\PickRepo;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class PickRepoTest extends WPBB_UnitTestCase {
  private PlayRepo $play_repo;
  private PickRepo $pick_repo;

  public function set_up(): void {
    parent::set_up();

    $this->play_repo = new PlayRepo();
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

    $play = new Play([
      'bracket_id' => $bracket->id,
      'author' => 1,
      'total_score' => 5,
      'accuracy_score' => 0.3,
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

    $most_popular_picks = $this->pick_repo->get_most_popular_picks($bracket->id);
    print_r($most_popular_picks);
    $picks = $this->pick_repo->get_picks($play->id);
    print_r($picks[0]);
  }
}
