<?php

use PHPUnit\Framework\TestCase;
use WStrategies\BMB\Includes\Domain\Pick;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Domain\Team;

class PlayTest extends TestCase {
  public function test_should_return_picked_teams_in_ranked_order() {
    $play = new Play([
      'picks' => [
        new Pick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => 1,
          'winning_team' => new Team(['name' => 'team 1', 'id' => 1]),
        ]),
        new Pick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => 3,
          'winning_team' => new Team(['name' => 'team 3', 'id' => 3]),
        ]),
        new Pick([
          'round_index' => 0,
          'match_index' => 2,
          'winning_team_id' => 5,
          'winning_team' => new Team(['name' => 'team 5', 'id' => 5]),
        ]),
        new Pick([
          'round_index' => 0,
          'match_index' => 3,
          'winning_team_id' => 7,
          'winning_team' => new Team(['name' => 'team 7', 'id' => 7]),
        ]),
        new Pick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => 1,
          'winning_team' => new Team(['name' => 'team 1', 'id' => 1]),
        ]),
        new Pick([
          'round_index' => 1,
          'match_index' => 1,
          'winning_team_id' => 5,
          'winning_team' => new Team(['name' => 'team 5', 'id' => 5]),
        ]),
        new Pick([
          'round_index' => 2,
          'match_index' => 0,
          'winning_team_id' => 5,
          'winning_team' => new Team(['name' => 'team 5', 'id' => 5]),
        ]),
      ],
    ]);

    $ranked_teams = $play->get_ranked_teams();
    $this->assertEquals($ranked_teams[0]->id, 5);
    $this->assertEquals($ranked_teams[1]->id, 1);
    $this->assertEquals($ranked_teams[2]->id, 3);
    $this->assertEquals($ranked_teams[3]->id, 7);
  }
}
