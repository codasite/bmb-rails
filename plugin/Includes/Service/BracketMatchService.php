<?php

namespace WStrategies\BMB\Includes\Service;

use InvalidArgumentException;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\BracketMatchNodeInterface;

class BracketMatchService {
  /**
   * @param array<BracketMatchNodeInterface> $nodes_flat
   */
  public function match_node_2d(array $nodes_flat) {
    $arr_2d = [];
    foreach ($nodes_flat as $node) {
      $arr_2d[$node->get_round_index()][$node->get_match_index()] = $node;
    }
    return $arr_2d;
  }

  /**
   * @param array<BracketMatch> $matches
   * @param array<MatchPick> $picks
   */
  public function matches_from_picks(array $matches_flat, array $picks_flat) {
    $matches_2d = $this->match_node_2d($matches_flat);
    // Assume picks are sorted by round_index and match_index
    foreach ($picks_flat as $pick) {
      if (isset($matches_2d[$pick->round_index][$pick->match_index])) {
        $match = $matches_2d[$pick->round_index][$pick->match_index];
        if ($pick->winning_team_id === $match->team1->id) {
          $match->team1_wins = true;
        } elseif ($pick->winning_team_id === $match->team2->id) {
          $match->team2_wins = true;
        }
      } else {
        $team1_match = $this->get_prev_match(
          $matches_2d,
          $pick->round_index,
          $pick->match_index,
          0
        );
        $team2_match = $this->get_prev_match(
          $matches_2d,
          $pick->round_index,
          $pick->match_index,
          1
        );
        $matches_2d[$pick->round_index][$pick->match_index] = new BracketMatch([
          'round_index' => $pick->round_index,
          'match_index' => $pick->match_index,
          'team1' => $team1_match->get_winning_team(),
          'team2' => $team2_match->get_winning_team(),
          'team1_wins' =>
            $pick->winning_team_id === $team1_match->get_winning_team()->id,
          'team2_wins' =>
            $pick->winning_team_id === $team2_match->get_winning_team()->id,
        ]);
      }
    }
    return $matches_2d;
  }

  /**
   * @param array<BracketMatch> $matches_2d
   * @param int $round_index
   * @param int $match_index
   * @param int $team 0 or 1
   */
  public function get_prev_match(
    array $matches_2d,
    int $round_index,
    int $match_index,
    int $team
  ): BracketMatch {
    if ($team < 0 || $team > 1) {
      throw new InvalidArgumentException('team must be 0 or 1');
    }
    $prev_round_index = $round_index - 1;
    $prev_match_index = $match_index * 2 + $team;

    if (!isset($matches_2d[$prev_round_index][$prev_match_index])) {
      throw new InvalidArgumentException(
        'No match found at index: ' .
          $prev_match_index .
          ' in round: ' .
          $prev_round_index
      );
    }

    return $matches_2d[$prev_round_index][$prev_match_index];
  }
}
