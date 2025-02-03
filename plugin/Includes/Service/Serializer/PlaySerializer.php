<?php

namespace WStrategies\BMB\Includes\Service\Serializer;

use WStrategies\BMB\Includes\Domain\Play;

class PlaySerializer extends PostBaseSerializer {
  public function deserialize(array|string $data): Play {
    $obj_data = $this->get_object_data($data);
    return new Play($obj_data);
  }

  public function get_serialized_fields(): array {
    return array_merge(parent::get_serialized_fields(), [
      'bracket_id' => [
        'required' => true,
      ],
      'total_score',
      'accuracy_score',
      'busted_id',
      'is_printed',
      'is_bustable',
      'is_winner',
      'bmb_official',
      'is_tournament_entry',
      'is_paid',
      'bracket' => new BracketSerializer(),
      'busted_play' => new PlaySerializer(),
      'picks' => new PickSerializer(['many' => true, 'required' => true]),
    ]);
  }

  public function get_readonly_fields(): array {
    return array_merge(parent::get_readonly_fields(), [
      'total_score',
      'accuracy_score',
      'is_printed',
      'is_bustable',
      'is_winner',
      'bmb_official',
      'is_tournament_entry',
      'is_paid',
      'bracket',
      'busted_play',
    ]);
  }
}
