<?php

namespace WStrategies\BMB\Includes\Service\Serializer;

abstract class PostBaseSerializer extends ApiSerializerBase {
  public function get_serialized_fields(): array {
    return [
      'id',
      'title',
      'author',
      'status',
      'published_date',
      'slug',
      'author_display_name',
      'thumbnail_url',
      'url',
    ];
  }

  public function get_readonly_fields(): array {
    return [
      'id',
      'author',
      'status',
      'published_date',
      'slug',
      'author_display_name',
      'thumbnail_url',
      'url',
    ];
  }
}
