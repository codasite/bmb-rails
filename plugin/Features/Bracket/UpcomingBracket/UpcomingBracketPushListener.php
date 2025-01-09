<?php

namespace WStrategies\BMB\Features\Bracket\UpcomingBracket;

use WStrategies\BMB\Features\Notifications\Notification;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\User;

class UpcomingBracketPushListener implements
  UpcomingNotificationListenerInterface {
  public function notify(
    User $user,
    Bracket $bracket,
    Notification $notification
  ): void {
    // TODO: Implement push notification logic
  }
}
