<?php

namespace WStrategies\BMB\Includes\Service\Serializer;

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Domain\MatchPick;
use WStrategies\BMB\Includes\Domain\Team;

class TeamSerializer extends ApiSerializerBase {
  public function deserialize($data): object {
    $obj_data = $this->get_object_data($data);
    return new Team($obj_data);
  }
  public function get_serialized_fields(): array {
    return ['id', 'name'];
  }
}
