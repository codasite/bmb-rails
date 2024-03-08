<?php

namespace WStrategies\BMB\Includes\Service;

use InvalidArgumentException;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\BracketMatchNodeInterface;
use WStrategies\BMB\Includes\Domain\Pick;

class BracketMatchService {
  /**
   * @param array<BracketMatchNodeInterface> $nodes_flat
   * @return array<array<BracketMatchNodeInterface>>
   */
  public static function match_node_2d(array $nodes_flat) {
    $arr_2d = [];
    foreach ($nodes_flat as $node) {
      $arr_2d[$node->get_round_index()][$node->get_match_index()] = $node;
    }
    return $arr_2d;
  }

  /**
   * @param array<BracketMatchNodeInterface> $nodes_flat
   * @return array<BracketMatchNodeInterface>
   */
  public static function sort_match_node(array $nodes_flat) {
    if (empty($nodes_flat)) {
      return $nodes_flat;
    }
    if (self::is_sorted($nodes_flat)) {
      return $nodes_flat;
    }
    usort($nodes_flat, function ($a, $b) {
      if ($a->get_round_index() === $b->get_round_index()) {
        return $a->get_match_index() - $b->get_match_index();
      }
      return $a->get_round_index() - $b->get_round_index();
    });
    return $nodes_flat;
  }

  public static function is_sorted(array $nodes_flat) {
    $prev_round_index = -1;
    $prev_match_index = -1;
    foreach ($nodes_flat as $node) {
      if ($node->get_round_index() < $prev_round_index) {
        return false;
      }
      if (
        $node->get_round_index() === $prev_round_index &&
        $node->get_match_index() < $prev_match_index
      ) {
        return false;
      }
      $prev_round_index = $node->get_round_index();
      $prev_match_index = $node->get_match_index();
    }
    return true;
  }

  /**
   * Create a 2D array of matches to represent the bracket together with a set of picks.
   * NOTE: Because this method loops over the picks, it will only create matches that have been picked
   *
   * @param array<BracketMatch> $matches_flat
   * @param array<Pick> $picks_flat
   */
  public function matches_2d_from_picks(
    array $matches_flat,
    array $picks_flat
  ) {
    /**
     * @var array<array<BracketMatch>> $matches_2d
     */
    $matches_2d = $this->match_node_2d($matches_flat);
    // Ensure picks are sorted by round_index and match_index
    /**
     * @var array<Pick> $sorted_picks_flat
     */
    $sorted_picks_flat = $this->sort_match_node($picks_flat);
    // Sorts picks by round_index and match_index
    foreach ($sorted_picks_flat as $pick) {
      // Check if the match for this pick exists
      if (
        isset($matches_2d[$pick->get_round_index()][$pick->get_match_index()])
      ) {
        $match =
          $matches_2d[$pick->get_round_index()][$pick->get_match_index()];
        // Populate missing teams
        if (!$match->team1) {
          $match->team1 = $this->get_prev_match(
            $matches_2d,
            $pick->get_round_index(),
            $pick->get_match_index(),
            0
          )->get_winning_team();
        }
        if (!$match->team2) {
          $match->team2 = $this->get_prev_match(
            $matches_2d,
            $pick->get_round_index(),
            $pick->get_match_index(),
            1
          )->get_winning_team();
        }
        if ($match->team1 && $match->team1->equals($pick->get_winning_team())) {
          $match->set_team1_wins();
        } elseif (
          $match->team2 &&
          $match->team2->equals($pick->get_winning_team())
        ) {
          $match->set_team2_wins();
        } else {
          throw new InvalidArgumentException(
            'Invalid pick. Winning team not found in match'
          );
        }
      } else {
        $team1_match = $this->get_prev_match(
          $matches_2d,
          $pick->get_round_index(),
          $pick->get_match_index(),
          0
        );
        $team2_match = $this->get_prev_match(
          $matches_2d,
          $pick->get_round_index(),
          $pick->get_match_index(),
          1
        );
        $team1 = $team1_match->get_winning_team();
        $team2 = $team2_match->get_winning_team();
        if (!$team1 || !$team2) {
          throw new InvalidArgumentException(
            'Both teams must be set for a match to be created'
          );
        }
        $team1_wins =
          $team1 !== null && $team1->equals($pick->get_winning_team());
        $team2_wins =
          $team2 !== null && $team2->equals($pick->get_winning_team());
        if ($team1_wins === $team2_wins) {
          throw new InvalidArgumentException(
            'Only one team can win a match. Pick: ' .
              print_r($pick, true) .
              ' Team1: ' .
              print_r($team1, true) .
              ' Team2: ' .
              print_r($team2, true)
          );
        }
        $matches_2d[$pick->get_round_index()][
          $pick->get_match_index()
        ] = new BracketMatch([
          'round_index' => $pick->get_round_index(),
          'match_index' => $pick->get_match_index(),
          'team1' => $team1,
          'team2' => $team2,
          'team1_wins' => $team1_wins,
          'team2_wins' => $team2_wins,
        ]);
      }
    }
    return $matches_2d;
  }

  /**
   * @param array<array<BracketMatch>> $matches_2d
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

  /**
   * @param array<array<mixed>> $arr_2d
   * @param array<BracketMatchNodeInterface> $nodes_flat
   *
   * @return array<array<mixed>> $arr_2d
   */
  public function filter_2d_array(array $arr_2d, array $nodes_flat): array {
    $filtered = [];
    foreach ($nodes_flat as $node) {
      $round = $node->get_round_index();
      $match = $node->get_match_index();
      if (!isset($arr_2d[$round][$match])) {
        throw new InvalidArgumentException(
          'No match found at index: ' . $match . ' in round: ' . $round
        );
      }
      $filtered[$round][$match] = $arr_2d[$round][$match];
    }
    return $filtered;
  }
}
