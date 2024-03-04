<?php

namespace WStrategies\BMB\Includes\Repository;

use WStrategies\BMB\Includes\Domain\User;

class UserRepo {
  public function get_by_id(int $id): ?User {
    return User::from_wp_user(get_user_by('id', $id));
  }

  public function get_current_user(): ?User {
    return User::from_wp_user(wp_get_current_user());
  }
}
