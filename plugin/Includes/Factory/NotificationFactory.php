<?php

namespace WStrategies\BMB\Includes\Factory;
use WStrategies\BMB\Features\Notifications\NotificationSubscription;
use WStrategies\BMB\Features\Notifications\NotificationType;
use WStrategies\BMB\Includes\Domain\ValidationException;

class NotificationFactory {
  public static function create($data = []): NotificationSubscription {
    $errors = self::validate($data);
    if (count($errors)) {
      throw new ValidationException(implode(', ', $errors));
    }
    return new NotificationSubscription($data);
  }

  public static function validate($data = []): array {
    $errors = [];
    if (!$data['user_id'] || !get_user_by('id', $data['user_id'])) {
      $errors[] = 'user_id for existing user is required';
    }
    if (!$data['post_id'] || !get_post($data['post_id'])) {
      $errors[] = 'post_id for existing post is required';
    }
    if (!$data['notification_type']) {
      $errors[] = 'notification_type is required';
    }
    if (
      !$data['notification_type'] instanceof NotificationType &&
      !NotificationType::is_valid($data['notification_type'])
    ) {
      $errors[] = 'notification_type is invalid';
    }
    return $errors;
  }
}
