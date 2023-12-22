<?php
namespace WStrategies\BMB\Includes\Service;

use WStrategies\BMB\Includes\Domain\Bracket;

interface ScoreServiceInterface {
  public function score_bracket_plays(
    Bracket|int|null $bracket,
    bool $set_winners = false
  ): int;
}
