<?php

namespace WStrategies\BMB\Features\Notifications\Domain;

interface NotificationChannelInterface {
  /**
   * Handles a notification for this channel
   *
   * @param Notification $notification The notification to handle
   * @return mixed The result of handling the notification (may vary by channel)
   * @throws \Exception if there's an error handling the notification
   */
  public function handle_notification(Notification $notification): mixed;
}
