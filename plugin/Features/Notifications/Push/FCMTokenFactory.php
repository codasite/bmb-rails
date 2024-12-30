<?php

namespace WStrategies\BMB\Features\Notifications\Push;

use WStrategies\BMB\Includes\Domain\ValidationException;

class FCMTokenFactory {
  public static function create($data = []): FCMToken {
    $errors = self::validate($data);
    if (count($errors)) {
      throw new ValidationException(implode(', ', $errors));
    }
    return new FCMToken($data);
  }

  public static function validate($data = []): array {
    $errors = [];

    // Required fields
    if (!isset($data['user_id']) || !get_user_by('id', $data['user_id'])) {
      $errors[] = 'user_id for existing user is required';
    }

    if (empty($data['device_id']) || trim($data['device_id']) === '') {
      $errors[] = 'device_id is required';
    } elseif (strlen($data['device_id']) > 255) {
      $errors[] = 'device_id must be less than 255 characters';
    }

    if (empty($data['token']) || trim($data['token']) === '') {
      $errors[] = 'token is required';
    } elseif (strlen($data['token']) > 255) {
      $errors[] = 'token must be less than 255 characters';
    }

    if (empty($data['device_type']) || trim($data['device_type']) === '') {
      $errors[] = 'device_type is required';
    } elseif (!in_array($data['device_type'], ['ios', 'android'])) {
      $errors[] = 'device_type must be either ios or android';
    }

    // Optional fields validation
    if (
      isset($data['device_name']) &&
      $data['device_name'] !== null &&
      strlen($data['device_name']) > 255
    ) {
      $errors[] = 'device_name must be less than 255 characters';
    }

    if (
      isset($data['app_version']) &&
      $data['app_version'] !== null &&
      strlen($data['app_version']) > 50
    ) {
      $errors[] = 'app_version must be less than 50 characters';
    }

    // Datetime validation
    if (
      !empty($data['created_at']) &&
      strtotime($data['created_at']) === false
    ) {
      $errors[] = 'created_at must be a valid datetime';
    }

    if (
      !empty($data['last_used_at']) &&
      strtotime($data['last_used_at']) === false
    ) {
      $errors[] = 'last_used_at must be a valid datetime';
    }

    return $errors;
  }
}
