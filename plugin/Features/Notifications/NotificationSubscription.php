<?php

namespace WStrategies\BMB\Features\Notifications;

class NotificationSubscription {
  public int|null $id;
  public int $user_id;
  public int $post_id;
  public NotificationType $notification_type;

  public function __construct($data = []) {
    $this->id = $data['id'] ?? null ? (int) $data['id'] : null;
    $this->user_id = (int) $data['user_id'];
    $this->post_id = (int) $data['post_id'];
    $notification_type = $data['notification_type'];
    if ($data['notification_type'] instanceof NotificationType) {
      $this->notification_type = $data['notification_type'];
    } elseif (is_string($notification_type)) {
      $this->notification_type = NotificationType::from(
        $data['notification_type']
      );
    }
  }
}
