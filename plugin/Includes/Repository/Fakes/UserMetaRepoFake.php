<?php

namespace WStrategies\BMB\Includes\Repository\Fakes;

use WStrategies\BMB\Includes\Repository\UserMetaRepo;

class UserMetaRepoFake extends UserMetaRepo {
  private array $meta = [];
  public function __construct(private readonly string $meta_key) {
    parent::__construct($meta_key);
  }

  public function get(int $user_id): string {
    return $this->meta[$user_id][$this->meta_key] ?? '';
  }

  public function set(int $user_id, string $value): void {
    $this->meta[$user_id][$this->meta_key] = $value;
  }
}
