<?php
namespace WStrategies\BMB\Features\Bracket\UpcomingBracket;

use WStrategies\BMB\Features\Notifications\Notification;

interface NotificationListenerInterface {
  public function notify(Notification $notification): void;
}
