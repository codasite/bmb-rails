<?php

namespace WStrategies\BMB\Features\Notifications\Push;

class FCMToken {
  public ?int $id;
  public int $user_id;
  public string $device_id;
  public string $token;
  public string $device_type;
  public ?string $device_name;
  public ?string $app_version;
  public string $created_at;
  public string $last_used_at;

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
