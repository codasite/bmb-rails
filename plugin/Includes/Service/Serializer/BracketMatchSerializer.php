<?php

namespace WStrategies\BMB\Includes\Service\Serializer;

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\Play;

class BracketMatchSerializer extends ApiSerializerBase {
  private TeamSerializer $team_serializer;
  public function __construct($args = []) {
    $this->team_serializer = $args['team_serializer'] ?? new TeamSerializer();
  }
  // public function serialize(object $bracket): array {
  //   if (!$bracket instanceof BracketMatch) {
  //     throw new \Exception('Invalid data type');
  //   }
  // }

  public function deserialize($data): BracketMatch {
    $obj_data = $this->get_object_data($data);
    return new BracketMatch($obj_data);
  }

  public function get_serialized_fields(): array {
    return [
      'id',
      'round_index',
      'match_index',
      'team1' => ['serializer' => $this->team_serializer],
      'team2' => ['serializer' => $this->team_serializer],
    ];
  }
}
