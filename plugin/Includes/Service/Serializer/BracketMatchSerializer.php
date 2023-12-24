<?php

namespace WStrategies\BMB\Includes\Service\Serializer;

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\BracketPlay;

class BracketMatchSerializer extends ApiSerializerBase {
  // public function serialize(object $bracket): array {
  //   if (!$bracket instanceof BracketMatch) {
  //     throw new \Exception('Invalid data type');
  //   }
  // }

  // public function deserialize($data): object {
  // }

  public function get_serialized_fields(): array {
    return ['id', 'round_index', 'match_index'];
  }
}
