<?php

namespace WStrategies\BMB\Features\VotingBracket\Domain;

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\PickRepo;

class VotingBracketService {
  private BracketRepo $bracket_repo;
  private PickRepo $pick_repo;
  public function __construct(array $args = []) {
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
    $this->pick_repo = $args['pick_repo'] ?? $this->bracket_repo->pick_repo;
  }

  /**
   * Returns whether the bracket has plays for the live round.
   * @return bool
   */
  public function has_plays_for_live_round(Bracket $bracket): bool {
    $num_picks = $this->pick_repo->get_num_picks_for_round(
      $bracket->id,
      $bracket->live_round_index
    );
    return $num_picks > 0;
  }

  /**
   * Returns whether any ties exist for most popular pick
   * @return bool
   */
  public function has_ties_for_live_round(Bracket $bracket): bool {
    return $this->pick_repo->has_tie_for_most_popular_pick($bracket->id, [
      'round_index' => $bracket->live_round_index,
    ]);
  }

  /**
   * Complete the current round for the given bracket ID.
   *
   * @param int $bracket_id Bracket ID.
   * @return Bracket The updated bracket object.
   */
  public function complete_bracket_round($bracket_id): ?Bracket {
    $bracket = $this->bracket_repo->get($bracket_id);
    $current_round = $bracket->live_round_index;
    $bracket->live_round_index += 1;
    // If the current round is the last round, set the bracket status to 'complete'.
    if ($bracket->live_round_index === $bracket->get_num_rounds()) {
      $bracket->status = 'complete';
    }
    // Calculate the most popular picks for the current round.
    // Save them to the bracket.
    $mpp = $this->pick_repo->get_most_popular_picks($bracket->id, [
      'round_index' => $current_round,
    ]);
    $bracket->results = array_merge($bracket->results, $mpp);

    $bracket = $this->bracket_repo->update($bracket);
    return $bracket;
  }
}
