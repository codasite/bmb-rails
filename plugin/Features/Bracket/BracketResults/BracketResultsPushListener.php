<?php

namespace WStrategies\BMB\Features\Bracket\BracketResults;

use WStrategies\BMB\Includes\Domain\PickResult;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Domain\User;

class BracketResultsPushListener implements
  BracketResultsNotificationListenerInterface {
  public function notify(User $user, Play $play, PickResult $result): void {
    // TODO: Implement push notification logic
  }
}
