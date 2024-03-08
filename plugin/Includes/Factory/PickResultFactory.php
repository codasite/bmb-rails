<?php
namespace WStrategies\BMB\Includes\Factory;

use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Pick;
use WStrategies\BMB\Includes\Domain\PickResult;

class PickResultFactory {
  /**
   * @param BracketMatch[][] $matches_2d
   * @param Pick[] $picks
   */
  // NOTE: the tests for this wont run (updated signature)
  public function create_match_pick_results(
    array $matches_2d,
    array $picks
  ): array {
    $match_pick_results = [];

    foreach ($picks as $pick) {
      if (isset($matches_2d[$pick->round_index][$pick->match_index])) {
        $match = $matches_2d[$pick->round_index][$pick->match_index];
        $match_pick_results[] = new PickResult($match, $pick);
      }
    }

    return $match_pick_results;
  }
}
