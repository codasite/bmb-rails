<?php

namespace WStrategies\BMB\Features\Notifications\Application;

use WStrategies\BMB\Features\Notifications\Domain\Notification;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Features\Notifications\Domain\NotificationChannelInterface;
use WStrategies\BMB\Features\Notifications\Infrastructure\NotificationRepo;

/**
 * Manages notification operations and business logic.
 */
class NotificationManager implements NotificationChannelInterface {
  private NotificationRepo $notification_repo;

  public function __construct(array $args = []) {
    $this->notification_repo =
      $args['notification_repo'] ?? new NotificationRepo();
  }

  /**
   * Handles storing a notification in the database
   *
   * @param Notification $notification The notification to store
   * @return Notification|null The stored notification or null on failure
   */
  public function handle_notification(
    Notification $notification
  ): ?Notification {
    return $this->create_notification($notification);
  }

  /**
   * @internal
   * Internal method that creates a new notification.
   */
  private function create_notification(
    Notification $notification
  ): ?Notification {
    // Check if user exists
    return $this->notification_repo->add($notification);
  }

  /**
   * Marks a notification as read.
   *
   * @param int $notification_id The ID of the notification to mark as read
   * @return Notification|null The updated notification or null if not found
   */
  public function mark_as_read(int $notification_id): ?Notification {
    return $this->notification_repo->update($notification_id, [
      'is_read' => true,
    ]);
  }

  /**
   * Marks all unread notifications for a user as read.
   *
   * @param int $user_id The user ID whose notifications to mark as read
   * @return int Number of notifications marked as read
   */
  public function mark_all_as_read(int $user_id): int {
    return $this->notification_repo->bulk_update(
      [
        'user_id' => $user_id,
        'is_read' => false,
      ],
      [
        'is_read' => true,
      ]
    );
  }
}
