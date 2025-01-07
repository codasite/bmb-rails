<?php
namespace WStrategies\BMB\Features\Notifications\NotificationListeners;

use WStrategies\BMB\Features\Notifications\Notification;

interface NotificationListenerInterface {
  public function notify(Notification $notification): void;
}
