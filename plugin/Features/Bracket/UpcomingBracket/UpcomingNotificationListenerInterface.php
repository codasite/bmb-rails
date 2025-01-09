<?php
namespace WStrategies\BMB\Features\Bracket\UpcomingBracket;

use WStrategies\BMB\Features\Notifications\Notification;

interface UpcomingNotificationListenerInterface {
  public function notify(Notification $notification): void;
}
