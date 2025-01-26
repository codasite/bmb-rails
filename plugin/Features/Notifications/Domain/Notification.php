<?php

namespace WStrategies\BMB\Features\Notifications\Domain;

use DateTime;

/**
 * Domain class representing a notification.
 *
 * This class represents a notification that can be sent to users through various channels.
 * Each notification has a type, message content, and associated metadata.
 */
class Notification {
  /** @var string|null The unique identifier for this notification */
  public ?string $id;

  /** @var int The WordPress user ID this notification belongs to */
  public int $user_id;

  /** @var string The notification title */
  public string $title;

  /** @var string The notification message content */
  public string $message;

  /** @var DateTime The timestamp when this notification was created */
  public DateTime $timestamp;

  /** @var bool Whether the notification has been read */
  public bool $is_read;

  /** @var string|null Optional link associated with the notification */
  public string|null $link;

  /** @var NotificationType The type of notification */
  public NotificationType $notification_type;

  /**
   * Creates a new Notification instance.
   *
   * @param array $data {
   *     @type string|null     $id               Optional. Notification ID.
   *     @type int            $user_id          Required. WordPress user ID.
   *     @type string         $title            Required. Notification title.
   *     @type string         $message          Required. Notification message.
   *     @type string|DateTime $timestamp       Required. Creation timestamp.
   *     @type bool           $is_read         Optional. Read status.
   *     @type string|null     $link            Optional. Associated link.
   *     @type string|NotificationType $notification_type Required. Type of notification.
   * }
   */
  public function __construct($data = []) {
    $this->id = $data['id'] ?? null;
    $this->user_id = (int) $data['user_id'];
    $this->title = $data['title'];
    $this->message = $data['message'];

    // More defensive timestamp handling
    if (isset($data['timestamp'])) {
      $this->timestamp =
        $data['timestamp'] instanceof DateTime
          ? $data['timestamp']
          : new DateTime($data['timestamp']);
    } else {
      $this->timestamp = new DateTime(); // Default to current time
    }

    $this->is_read = $data['is_read'] ?? false;
    $this->link = $data['link'] ?? null;

    $notification_type = $data['notification_type'];
    if ($notification_type instanceof NotificationType) {
      $this->notification_type = $notification_type;
    } elseif (is_string($notification_type)) {
      $this->notification_type = NotificationType::from($notification_type);
    }
  }

  /**
   * Marks the notification as read.
   */
  public function mark_as_read(): void {
    $this->is_read = true;
  }

  /**
   * Converts the notification to an array representation.
   *
   * @return array Notification data as an associative array.
   */
  public function to_array(): array {
    return [
      'id' => $this->id,
      'user_id' => $this->user_id,
      'title' => $this->title,
      'message' => $this->message,
      'timestamp' => $this->timestamp->format('c'), // ISO 8601 format
      'is_read' => $this->is_read,
      'link' => $this->link,
      'notification_type' => $this->notification_type->value,
    ];
  }

  /**
   * Creates a new instance from an array of data.
   *
   * @param array $data The notification data.
   * @return self
   */
  public static function from_array(array $data): self {
    return new self($data);
  }
}
