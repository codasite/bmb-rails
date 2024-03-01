<?php

namespace WStrategies\BMB\Includes\Domain;

use WP_User;

class User {
  public int $id;
  public string $user_email;
  public string $first_name;
  public string $last_name;
  public string $display_name;
  public string $user_login;

  public function __construct(array $args = []) {
    $this->id = $args['id'];
    $this->user_email = $args['user_email'];
    $this->first_name = $args['first_name'];
    $this->last_name = $args['last_name'];
    $this->display_name = $args['display_name'];
    $this->user_login = $args['user_login'];
  }

  public static function from_wp_user(WP_User $wp_user): User {
    return new User([
      'id' => $wp_user->ID,
      'user_email' => $wp_user->user_email,
      'first_name' => $wp_user->first_name,
      'last_name' => $wp_user->last_name,
      'display_name' => $wp_user->display_name,
      'user_login' => $wp_user->user_login,
    ]);
  }
}
