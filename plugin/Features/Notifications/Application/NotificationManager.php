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

  public function __construct(array $args = []) {
    $this->notification_repo =
      $args['notification_repo'] ?? new NotificationRepo();
  }

  /**
   * Creates a new notification.
   *
   * @param int $user_id User ID to notify
   * @param string $title Notification title
   * @param string $message Notification message
   * @param NotificationType $notification_type Type of notification
   * @param string $link Associated link
   * @return Notification|null The created notification or null on failure
   */
  public function create_notification(
    int $user_id,
    string $title,
    string $message,
    NotificationType $notification_type,
    string $link
  ): ?Notification {
    // Check if user exists
    if (!get_user_by('id', $user_id)) {
      return null;
    }

    try {
      $data = [
        'user_id' => $user_id,
        'title' => $title,
        'message' => $message,
        'notification_type' => $notification_type->value,
        'link' => $link,
      ];

      $notification = new Notification($data);
      return $this->notification_repo->add($notification);
    } catch (\Exception $e) {
      return null;
    }
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
