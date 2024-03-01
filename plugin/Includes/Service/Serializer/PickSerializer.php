<?php

namespace WStrategies\BMB\Includes\Service\Serializer;

use WStrategies\BMB\Includes\Domain\Pick;

class PickSerializer extends ApiSerializerBase {
  // public function serialize(object $bracket): array {
  //   if (!$bracket instanceof Pick) {
  //     throw new \Exception('Invalid data type');
  //   }
  // }

  public function deserialize($data): Pick {
    $obj_data = $this->get_object_data($data);
    return new Pick($obj_data);
  }
  public function get_serialized_fields(): array {
    return ['id', 'round_index', 'match_index', 'winning_team_id'];
  }
}
