<?php

namespace WStrategies\BMB\Includes\Domain;

use WStrategies\BMB\Includes\Domain\Pick;

class BracketResult extends Pick {
  public ?float $winning_team_pick_percent;

  public function __construct($data = []) {
    parent::__construct($data);
    $this->winning_team_pick_percent = isset($data['winning_team_pick_percent'])
      ? (float) $data['winning_team_pick_percent']
      : null;
  }
  public static function from_array($data): BracketResult {
    return new BracketResult(self::hydrate_array($data));
  }
}
