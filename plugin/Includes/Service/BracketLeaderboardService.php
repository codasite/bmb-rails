<?php

namespace WStrategies\BMB\Includes\Service;

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Repository\BracketPlayRepo;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Domain\Team;

class BracketLeaderboardService {
  private BracketRepo $bracket_repo;
  private BracketPlayRepo $play_repo;
  private Bracket $bracket;
  private array $plays;

  public function __construct(int $bracket_id, array $args = []) {
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
    $this->play_repo = $args['play_repo'] ?? new BracketPlayRepo();
    $this->bracket = $this->bracket_repo->get($bracket_id);
    if (!$this->bracket) {
      throw new \Exception('Bracket not found');
    }
  }

  public function get_bracket(): Bracket {
    return $this->bracket;
  }

  public function get_plays(): array {
    if (isset($this->plays)) {
      return $this->plays;
    }
    $query = [
      'post_status' => 'publish',
      'bracket_id' => $this->bracket->id,
      'busted_play_id' => [
        'compare' => 'NOT EXISTS',
      ],
      'orderby' => 'accuracy_score',
      'order' => 'DESC',
    ];

    if ($this->bracket->results_first_updated_at) {
      $query['date_query'] = [
        [
          'column' => 'post_modified_gmt',
          'before' => $this->bracket->results_first_updated_at->format(
            'Y-m-d H:i:s'
          ),
          'inclusive' => true,
        ],
      ];
    }
    $plays = $this->play_repo->get_all($query, [
      'fetch_picks' => true,
    ]);

    $this->plays = $plays;
    return $plays;
  }
}
