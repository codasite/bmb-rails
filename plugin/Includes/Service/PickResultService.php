<?php

namespace WStrategies\BMB\Includes\Service;

use WStrategies\BMB\Includes\Domain\PickResult;
use WStrategies\BMB\Includes\Domain\Play;

class PickResultService {
  /**
   * @param array<PickResult> $results
   */
  public function get_pick_result_for_play(
    array $results,
    Play $play,
    bool $final_winning_team_only = true
  ): PickResult|null {
    $final_winning_team_id = $play->get_winning_team()->id;
    if (!$final_winning_team_id) {
      throw new \Exception('Winning team id is required');
    }
    if ($final_winning_team_only) {
      return $this->get_pick_result_for_single_team(
        $results,
        $final_winning_team_id
      );
    }
    return $this->get_pick_result_for_single_team(
      $results,
      $final_winning_team_id
    );
  }

  /**
   * This function returns the match pick result given a single team id (assumed to be the final winning pick of a play)
   *
   * @param array<PickResult> $results
   * @param int $team_id
   *
   * @return PickResult|null
   */
  public function get_pick_result_for_single_team(
    array $results,
    int $team_id
  ): PickResult|null {
    $result = null;
    $winning_team_map = $this->get_winning_team_map($results);
    $losing_team_map = $this->get_losing_team_map($results);
    if (isset($winning_team_map[$team_id])) {
      $result = $winning_team_map[$team_id];
    } elseif (isset($losing_team_map[$team_id])) {
      $result = $losing_team_map[$team_id];
    }
    return $result;
  }

  /**
   * This function returns the match pick result given an array of team ids.
   * team_ids is assumed to be a play's winning picks in ranked order. For example [5, 1, 0, 2, 3]
   * where team 5 is the final winning team, team 1 is the second place team, and so on.
   */
  public function get_pick_result_for_many_teams(
    array $results,
    array $team_ids
  ) {
    $result = null;
    $winning_team_map = $this->get_winning_team_map($results);
    $losing_team_map = $this->get_losing_team_map($results);
    foreach ($team_ids as $team_id) {
      if (isset($winning_team_map[$team_id])) {
        $result = $winning_team_map[$team_id];
        break;
      } elseif (isset($losing_team_map[$team_id])) {
        $result = $losing_team_map[$team_id];
        break;
      }
    }
    return $result;
  }
  /**
   * This function returns a mapping of team ids to the most recent match pick result where that team won
   * @param array<PickResult> $pick_results
   * @return array<array<PickResult>>
   */
  public function get_winning_team_map(array $pick_results) {
    $team_map = [];
    foreach ($pick_results as $result) {
      $team_map[$result->get_winning_team()->id] = $result;
    }
    return $team_map;
  }

  /**
   * This function returns a mapping of team ids to the most recent match pick result where that team lost
   * @param array<PickResult> $pick_results
   * @return array<array<PickResult>>
   */
  public function get_losing_team_map(array $pick_results) {
    $team_map = [];
    foreach ($pick_results as $result) {
      $team_map[$result->get_losing_team()->id] = $result;
    }
    return $team_map;
  }
}
