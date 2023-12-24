<?php

namespace WStrategies\BMB\Includes\Service\Serializer;

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Domain\MatchPick;

class MatchPickSerializer extends ApiSerializerBase {
  // public function serialize(object $bracket): array {
  //   if (!$bracket instanceof MatchPick) {
  //     throw new \Exception('Invalid data type');
  //   }
  // }

  public function deserialize($data): object {
    throw new \Exception('Not implemented');
  }
  public function get_serialized_fields(): array {
    return ['id', 'round_index', 'match_index', 'winning_team_id'];
  }
}
