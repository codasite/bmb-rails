<?php

namespace WStrategies\BMB\Includes\Repository;

class UserMetaRepo {
  public function __construct(private string $meta_key) {
  }

  public function get(int $user_id): string {
    return get_user_meta($user_id, $this->meta_key, true);
  }

  public function set(int $user_id, string $value): void {
    update_user_meta($user_id, $this->meta_key, $value);
  }
}
