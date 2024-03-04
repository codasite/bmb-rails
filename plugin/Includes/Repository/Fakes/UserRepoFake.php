<?php

namespace WStrategies\BMB\Includes\Repository\Fakes;

use WStrategies\BMB\Includes\Domain\Fakes\UserFake;
use WStrategies\BMB\Includes\Domain\User;
use WStrategies\BMB\Includes\Repository\UserRepo;

class UserRepoFake extends UserRepo {
  public function __construct(private User $user = new UserFake()) {
  }

  public function get_by_id(int $id): ?User {
    return $this->user;
  }

  public function get_current_user(): ?User {
    return $this->user;
  }
}
