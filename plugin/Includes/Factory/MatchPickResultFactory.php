<?php
namespace WStrategies\BMB\Includes\Factory;

use ValueError;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Domain\MatchPick;
use WStrategies\BMB\Includes\Domain\MatchPickResult;
use WStrategies\BMB\Includes\Service\BracketMatchService;

class MatchPickResultFactory {
  private BracketMatchService $match_service;

  public function __construct($args = []) {
    $this->match_service = $args['match_service'] ?? new BracketMatchService();
  }

  public function create_match_pick_result(
    BracketMatch $match,
    MatchPick $pick
  ): MatchPickResult {
    if ($match->get_round_index() !== $pick->get_round_index()) {
      throw new ValueError('Round index mismatch');
    }
    if ($match->get_match_index() !== $pick->get_match_index()) {
      throw new ValueError('Match index mismatch');
    }
    if (!$match->has_results()) {
      throw new ValueError(
        'Match results not set. Populate match results first using BracketMatchService->matches_from_picks()'
      );
    }
    return new MatchPickResult([
      'round_index' => $match->get_round_index(),
      'match_index' => $match->get_match_index(),
      'winning_team' => $match->get_winning_team(),
      'losing_team' => $match->get_losing_team(),
      'picked_team' => $pick->get_winning_team(),
    ]);
  }

  /**
   * @param BracketMatch[][] $matches
   * @param MatchPick[] $picks
   */
  // NOTE: the tests for this wont run (updated signature)
  public function create_match_pick_results(
    array $matches,
    array $picks
  ): array {
    $match_pick_results = [];

    foreach ($picks as $pick) {
      $match = $matches[$pick->round_index][$pick->match_index];
      $match_pick_results[] = $this->create_match_pick_result($match, $pick);
    }

    return $match_pick_results;
  }
}
