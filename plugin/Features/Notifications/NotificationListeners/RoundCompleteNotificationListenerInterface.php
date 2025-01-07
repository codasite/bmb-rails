<?php
namespace WStrategies\BMB\Features\Notifications\NotificationListeners;

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Domain\User;

interface RoundCompleteNotificationListenerInterface {
  public function notify(Bracket $bracket, Play $play): void;
}
