<?php

namespace WStrategies\BMB\Includes\Service;

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Repository\BracketRepo;

class MostPopularPicksService {
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

  public function get_most_popular_picks(): array {

    return [];
  }
}
