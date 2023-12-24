<?php

namespace WStrategies\BMB\Includes\Service\Serializer;

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Domain\MatchPick;
use WStrategies\BMB\Includes\Domain\PostBase;
use WStrategies\BMB\Includes\Domain\Team;

class PostBaseSerializer extends ApiSerializerBase {
  // public function deserialize($data): object {
  // }

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
}
