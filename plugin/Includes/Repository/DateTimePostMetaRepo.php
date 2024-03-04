<?php

namespace WStrategies\BMB\Includes\Repository;

use DateTimeImmutable;

class DateTimePostMetaRepo {
  private string $meta_key;

  public function __construct(string $meta_key) {
    $this->meta_key = $meta_key;
  }

  /**
   * @throws \Exception
   */
  public function get(int $post_id): DateTimeImmutable {
    $value = get_post_meta($post_id, $this->meta_key, true);
    if ($value) {
      return new DateTimeImmutable($value);
    } else {
      return new DateTimeImmutable('1970-01-01');
    }
  }

  public function set(int $post_id, DateTimeImmutable $value): void {
    update_post_meta($post_id, $this->meta_key, $value->format('Y-m-d H:i:s'));
  }

  public function set_to_now(int $post_id): void {
    $this->set($post_id, new DateTimeImmutable());
  }
}
