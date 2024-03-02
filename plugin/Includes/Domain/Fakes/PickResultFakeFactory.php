<?php

namespace WStrategies\BMB\Includes\Domain\Fakes;

use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Pick;
use WStrategies\BMB\Includes\Domain\PickResult;
use WStrategies\BMB\Includes\Domain\Team;

class PickResultFakeFactory {
  public static function get_incorrect_pick_result() {
    return new PickResult(
      new BracketMatch([
        'round_index' => 1,
        'match_index' => 2,
        'team1_wins' => 1,
        'team2_wins' => 0,
        'team1' => new Team(['name' => 'team 1', 'id' => 1]),
        'team2' => new Team(['name' => 'team 2', 'id' => 2]),
      ]),
      new Pick([
        'round_index' => 1,
        'match_index' => 2,
        'winning_team_id' => 2,
        'winning_team' => new Team(['name' => 'team 2', 'id' => 2]),
      ])
    );
  }
  public static function get_correct_pick_result() {
    return new PickResult(
      new BracketMatch([
        'round_index' => 1,
        'match_index' => 2,
        'team1_wins' => 1,
        'team2_wins' => 0,
        'team1' => new Team(['name' => 'team 1', 'id' => 1]),
        'team2' => new Team(['name' => 'team 2', 'id' => 2]),
      ]),
      new Pick([
        'round_index' => 1,
        'match_index' => 2,
        'winning_team_id' => 1,
        'winning_team' => new Team(['name' => 'team 1', 'id' => 1]),
      ])
    );
  }
}
