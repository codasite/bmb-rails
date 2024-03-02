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
    Play $play
  ): PickResult|null {
    $ranked_teams = $play->get_ranked_teams();
    if (empty($ranked_teams)) {
      throw new \Exception('Play has no picks');
    }
    $team_ids = array_map(function ($team) {
      return $team->id;
    }, $ranked_teams);
    return $this->get_pick_result_for_many_teams($results, $team_ids);
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
    $team_results_map = $this->get_most_recent_pick_result_map($results);
    foreach ($team_ids as $team_id) {
      $result = $team_results_map[$team_id] ?? null;
      if ($result) {
        return $result;
      }
    }
    return null;
  }

  /**
   * Return a mapping of team ids to the pick result of the most recent match that team played in AND was picked for, whether they won or lost
   *
   * @param array<PickResult> $pick_results
   * @return array<int, PickResult>
   */
  public function get_most_recent_pick_result_map(array $pick_results) {
    $team_map = [];
    foreach ($pick_results as $result) {
      if ($result->picked_team_played()) {
        $team_map[$result->get_team1()->id] = $result;
        $team_map[$result->get_team2()->id] = $result;
      }
    }
    return $team_map;
  }
}
