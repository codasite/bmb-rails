<?php

namespace WStrategies\BMB\Features\Notifications\Push;

/**
 * Domain class representing a Firebase Cloud Messaging (FCM) token.
 *
 * This class represents a device token used for push notifications via Firebase Cloud Messaging.
 * Each token is associated with a specific user and device.
 */
class FCMToken {
  /** @var int|null The unique identifier for this token */
  public ?int $id;

  /** @var int The WordPress user ID associated with this token */
  public int $user_id;

  /** @var string The unique identifier for the device */
  public string $device_id;

  /** @var string The FCM token value */
  public string $token;

  /** @var string The device platform (ios|android) */
  public string $device_type;

  /** @var string|null The human-readable device name */
  public ?string $device_name;

  /** @var string|null The app version running on the device */
  public ?string $app_version;

  /** @var string The timestamp when this token was created */
  public string $created_at;

  /** @var string The timestamp when this token was last used */
  public string $last_used_at;

  /**
   * Creates a new FCMToken instance.
   *
   * @param array $data {
   *     @type int|null    $id           Optional. Token ID.
   *     @type int        $user_id      Required. WordPress user ID.
   *     @type string     $device_id    Required. Unique device identifier.
   *     @type string     $token        Required. FCM token value.
   *     @type string     $device_type  Required. Device platform (ios|android).
   *     @type string     $device_name  Optional. Human-readable device name.
   *     @type string     $app_version  Optional. App version string.
   *     @type string     $created_at   Optional. Creation timestamp.
   *     @type string     $last_used_at Optional. Last used timestamp.
   * }
   */
  public function __construct($data = []) {
    $this->id = isset($data['id']) ? (int) $data['id'] : null;
    $this->user_id = (int) $data['user_id'];
    $this->device_id = $data['device_id'];
    $this->token = $data['token'];
    $this->device_type = $data['device_type'];
    $this->device_name = $data['device_name'] ?? null;
    $this->app_version = $data['app_version'] ?? null;
    $this->created_at = $data['created_at'] ?? current_time('mysql');
    $this->last_used_at = $data['last_used_at'] ?? current_time('mysql');
  }

  /**
   * Converts the token to an array representation.
   *
   * @return array Token data as an associative array.
   */
  public function to_array(): array {
    return [
      'id' => $this->id,
      'user_id' => $this->user_id,
      'device_id' => $this->device_id,
      'token' => $this->token,
      'device_type' => $this->device_type,
      'device_name' => $this->device_name,
      'app_version' => $this->app_version,
      'created_at' => $this->created_at,
      'last_used_at' => $this->last_used_at,
    ];
  }
}
