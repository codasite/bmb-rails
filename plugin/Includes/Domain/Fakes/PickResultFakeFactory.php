<?php

namespace WStrategies\BMB\Includes\Domain\Fakes;

use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Pick;
use WStrategies\BMB\Includes\Domain\PickResult;
use WStrategies\BMB\Includes\Domain\Team;

class PickResultFakeFactory {
  public static function create_pick_result(
    $correct,
    $team1_id,
    $team2_id,
    $winning_team_id = null,
    $round_index = 0,
    $match_index = 0
  ) {
    // Build team names based on their IDs
    $team1_name = 'team ' . $team1_id;
    $team2_name = 'team ' . $team2_id;

    // Determine the winning team based on correctness and provided winning team ID
    $winning_team_id = $correct ? $team1_id : $team2_id;
    if ($winning_team_id !== null) {
      $winning_team_id = $winning_team_id;
    }
    $winning_team_name = 'team ' . $winning_team_id;

    return new PickResult(
      new BracketMatch([
        'round_index' => $round_index,
        'match_index' => $match_index,
        'team1_wins' => $correct ? 1 : 0,
        'team2_wins' => !$correct ? 1 : 0,
        'team1' => new Team(['name' => $team1_name, 'id' => $team1_id]),
        'team2' => new Team(['name' => $team2_name, 'id' => $team2_id]),
      ]),
      new Pick([
        'round_index' => $round_index,
        'match_index' => $match_index,
        'winning_team_id' => $winning_team_id,
        'winning_team' => new Team([
          'name' => $winning_team_name,
          'id' => $winning_team_id,
        ]),
      ])
    );
  }

  public static function get_incorrect_pick_result(
    $team1_id = 1,
    $team2_id = 2,
    $winning_team_id = 2,
    $round_index = 0,
    $match_index = 0
  ) {
    // Ensure the incorrect pick result by setting the winning team ID to the opposite team
    return self::create_pick_result(
      false,
      $team1_id,
      $team2_id,
      $winning_team_id,
      $round_index,
      $match_index
    );
  }

  public static function get_correct_pick_result(
    $team1_id = 1,
    $team2_id = 2,
    $winning_team_id = 1,
    $round_index = 0,
    $match_index = 0
  ) {
    // Ensure the correct pick result by setting the winning team ID to the expected winning team
    return self::create_pick_result(
      true,
      $team1_id,
      $team2_id,
      $winning_team_id,
      $round_index,
      $match_index
    );
  }
}
