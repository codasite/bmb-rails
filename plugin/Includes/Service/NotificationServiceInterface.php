<?php
namespace WStrategies\BMB\Includes\Service;

use WStrategies\BMB\Includes\Domain\Bracket;

interface NotificationServiceInterface {
  public function notify_bracket_results_updated(
    Bracket|int|null $bracket
  ): void;
}
