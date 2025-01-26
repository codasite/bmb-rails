<?php
namespace WStrategies\BMB\Features\Bracket\UpcomingBracket;

use WStrategies\BMB\Features\Notifications\NotificationSubscription;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\User;

interface UpcomingNotificationListenerInterface {
  public function notify(
    User $user,
    Bracket $bracket,
    NotificationSubscription $notification
  ): void;
}
