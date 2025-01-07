<?php
namespace WStrategies\BMB\Features\Notifications\NotificationListeners;

use WStrategies\BMB\Includes\Domain\PickResult;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Domain\User;

interface BracketResultsNotificationListenerInterface {
  public function notify(Play $play, PickResult $result): void;
}
