<?php

namespace WStrategies\BMB\Includes\Factory;

use WStrategies\BMB\Includes\Domain\PostBase;

abstract class PostBaseFactory implements FactoryInterface {
  protected function initialize(PostBase $obj, array $data): void {
    $obj->id = $data['id'] ?? null;
    $obj->title = $data['title'] ?? '';
    $obj->author = $data['author'] ?? null;
    $obj->status = $data['status'] ?? 'publish';
    $obj->published_date = $data['published_date'] ?? false;
    $obj->slug = $data['slug'] ?? '';
    $obj->author_display_name = $data['author_display_name'] ?? '';
    $obj->thumbnail_url = $data['thumbnail_url'] ?? false;
    $obj->url = $data['url'] ?? false;
  }
}
