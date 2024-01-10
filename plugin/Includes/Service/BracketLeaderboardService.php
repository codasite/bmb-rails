<?php

namespace WStrategies\BMB\Includes\Service;

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Domain\Team;

class BracketLeaderboardService {
  private BracketRepo $bracket_repo;
  private PlayRepo $play_repo;
  private Bracket $bracket;
  private array $plays;

  public function __construct(int $bracket_id = null, array $args = []) {
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
    $this->play_repo = $args['play_repo'] ?? new PlayRepo();
    if ($bracket_id) {
      $this->bracket = $this->bracket_repo->get($bracket_id);
      if (!$this->bracket) {
        throw new \Exception('Bracket not found');
      }
    }
  }

  public function get_bracket(): Bracket {
    return $this->bracket;
  }

  public function get_plays($bracket_id = null): array {
    $bracket_id = $bracket_id ?? $this->bracket->id;
    if (!$bracket_id) {
      throw new \Exception('Bracket ID is required');
    }
    if (isset($this->plays)) {
      return $this->plays;
    }
    $query = [
      'post_status' => 'publish',
      'bracket_id' => $bracket_id,
      'is_tournament_entry' => true,
      'orderby' => 'accuracy_score',
      'order' => 'DESC',
    ];

    $plays = $this->play_repo->get_all($query, [
      'fetch_picks' => true,
    ]);

    $this->plays = $plays;
    return $plays;
  }

  public function get_num_plays(array $args = []): int {
    $base_query = [
      'post_status' => 'publish',
      'is_tournament_entry' => true,
    ];
    if (isset($this->bracket)) {
      $base_query['bracket_id'] = $this->bracket->id;
    }
    $query = array_merge($base_query, $args);
    return $this->play_repo->get_count($query);
  }
}
