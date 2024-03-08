<?php

namespace WStrategies\BMB\Includes\Repository;

use WStrategies\BMB\Includes\Domain\User;

class UserRepo {
  public function get_by_id(int $id): ?User {
    $wp_user = get_user_by('id', $id);
    if (!$wp_user) {
      return null;
    }
    return User::from_wp_user($wp_user);
  }

  public function get_current_user(): ?User {
    if (!is_user_logged_in()) {
      return null;
    }
    return User::from_wp_user(wp_get_current_user());
  }
}
