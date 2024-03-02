<?php

namespace WStrategies\BMB\Includes\Domain\Fakes;

use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Pick;
use WStrategies\BMB\Includes\Domain\PickResult;
use WStrategies\BMB\Includes\Domain\Team;

class PickResultFakeFactory {
  // public static function create_pick_result(
  //   $team1_id,
  //   $team2_id,
  //   $winning_team_id,
  //   $picked_team_id,
  //   $round_index = 0,
  //   $match_index = 0
  // ) {
  public static function create_pick_result(array $args = []): PickResult {
    $team1_id = $args['team1_id'] ?? 1;
    $team2_id = $args['team2_id'] ?? 2;
    $winning_team_id = $args['winning_team_id'] ?? 1;
    $picked_team_id = $args['picked_team_id'] ?? 1;
    $round_index = $args['round_index'] ?? 0;
    $match_index = $args['match_index'] ?? 0;
    $team1_name = $args['team1_name'] ?? 'team ' . $team1_id;
    $team2_name = $args['team2_name'] ?? 'team ' . $team2_id;
    $picked_team_name = $args['picked_team_name'] ?? 'team ' . $picked_team_id;

    return new PickResult(
      new BracketMatch([
        'round_index' => $round_index,
        'match_index' => $match_index,
        'team1_wins' => $winning_team_id === $team1_id,
        'team2_wins' => $winning_team_id === $team2_id,
        'team1' => new Team(['name' => $team1_name, 'id' => $team1_id]),
        'team2' => new Team(['name' => $team2_name, 'id' => $team2_id]),
      ]),
      new Pick([
        'round_index' => $round_index,
        'match_index' => $match_index,
        'winning_team_id' => $winning_team_id,
        'winning_team' => new Team([
          'name' => $picked_team_name,
          'id' => $picked_team_id,
        ]),
      ])
    );
  }

  public static function get_correct_pick_result(
    array $args = [
      'team1_id' => 1,
      'team2_id' => 2,
      'round_index' => 0,
      'match_index' => 0,
    ]
  ) {
    return self::create_pick_result([
      'team1_id' => $args['team1_id'],
      'team2_id' => $args['team2_id'],
      'winning_team_id' => $args['team1_id'],
      'picked_team_id' => $args['team1_id'],
      'round_index' => $args['round_index'],
      'match_index' => $args['match_index'],
    ]);
  }

  public static function get_incorrect_pick_result(
    array $args = [
      'team1_id' => 1,
      'team2_id' => 2,
      'round_index' => 0,
      'match_index' => 0,
    ]
  ) {
    return self::create_pick_result([
      'team1_id' => $args['team1_id'],
      'team2_id' => $args['team2_id'],
      'winning_team_id' => $args['team1_id'],
      'picked_team_id' => $args['team2_id'],
      'round_index' => $args['round_index'],
      'match_index' => $args['match_index'],
    ]);
  }
}
