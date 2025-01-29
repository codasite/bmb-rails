<?php

namespace WStrategies\BMB\Features\Notifications\Application;

use WStrategies\BMB\Features\Notifications\Domain\Notification;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
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
   * Creates a new notification.
   *
   * @param array $data {
   *     Notification data.
   *     @type int    $user_id          Required. User ID to notify.
   *     @type string $title            Required. Notification title.
   *     @type string $message          Required. Notification message.
   *     @type string|NotificationType $notification_type Required. Type of notification.
   *     @type string $link             Optional. Associated link.
   * }
   * @return Notification|null The created notification or null on failure
   */
  public function create_notification(array $data): ?Notification {
    $notification = new Notification($data);
    return $this->notification_repo->add($notification);
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
