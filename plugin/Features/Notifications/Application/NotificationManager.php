<?php

namespace WStrategies\BMB\Features\Notifications\Application;

use WStrategies\BMB\Features\Notifications\Domain\Notification;
use WStrategies\BMB\Features\Notifications\Infrastructure\NotificationRepo;

/**
 * Manages notification operations and business logic.
 */
class NotificationManager {
  private NotificationRepo $notification_repo;

  public function __construct(NotificationRepo $notification_repo) {
    $this->notification_repo = $notification_repo;
  }

  /**
   * Marks a notification as read.
   *
   * @param string $notification_id The ID of the notification to mark as read
   * @return Notification|null The updated notification or null if not found
   */
  public function mark_as_read(string $notification_id): ?Notification {
    return $this->notification_repo->update($notification_id, [
      'is_read' => true,
    ]);
  }
}
