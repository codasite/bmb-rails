<?php

namespace WStrategies\BMB\Includes\Service\Serializer;

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Domain\MatchPick;
use WStrategies\BMB\Includes\Domain\Team;

class TeamSerializer extends ApiSerializerBase {
  // public function serialize(object $bracket): array {
  //   if (!$bracket instanceof Team) {
  //     throw new \Exception('Invalid data type');
  //   }
  // }

  // public function deserialize($data): object {
  // }
  public function get_serialized_fields(): array {
    return ['id', 'name'];
  }
}
