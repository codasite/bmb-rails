<?php

namespace WStrategies\BMB\Includes\Domain\Fakes;

use WStrategies\BMB\Includes\Domain\User;

class UserFake extends User {
  public function __construct(array $args = []) {
    $args['id'] = $args['id'] ?? 1;
    $args['user_email'] = $args['user_email'] ?? 'test@email.com';
    $args['first_name'] = $args['first_name'] ?? 'Test';
    $args['last_name'] = $args['last_name'] ?? 'User';
    $args['display_name'] = $args['display_name'] ?? 'Test User';
    $args['user_login'] = $args['user_login'] ?? 'testuser';
    parent::__construct($args);
  }
}
