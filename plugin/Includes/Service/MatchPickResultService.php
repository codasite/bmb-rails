<?php

namespace WStrategies\BMB\Includes\Service;

class MatchPickResultService {
  /**
   * This function returns a mapping of team ids to the latest match pick result where that team won
   * @param array<MatchPickResult> $match_pick_results
   * @return array<array<MatchPickResult>>
   */
  public function get_winning_team_map(array $match_pick_results) {
    $team_map = [];
    foreach ($match_pick_results as $result) {
      $team_map[$result->winning_team->id] = $result;
    }
    return $team_map;
  }

  public function get_losing_team_map(array $match_pick_results) {
    $team_map = [];
    foreach ($match_pick_results as $result) {
      $team_map[$result->losing_team->id] = $result;
    }
    return $team_map;
  }
}
