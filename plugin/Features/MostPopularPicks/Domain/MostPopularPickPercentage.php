<?php
namespace WStrategies\BMB\Features\MostPopularPicks\Domain;

class MostPopularPickPercentage {
  public function __construct(
    public int $round_index,
    public int $match_index,
    public int $team1_percent,
    public int $team2_percent
  ) {
  }
}
