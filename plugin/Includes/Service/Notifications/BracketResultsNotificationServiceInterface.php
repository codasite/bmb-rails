<?php
namespace WStrategies\BMB\Includes\Service\Notifications;

use WStrategies\BMB\Includes\Domain\Bracket;

interface BracketResultsNotificationServiceInterface {
  public function notify_bracket_results_updated(
    Bracket|int|null $bracket
  ): void;
}
