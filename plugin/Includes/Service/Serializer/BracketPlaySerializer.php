<?php

namespace WStrategies\BMB\Includes\Service\Serializer;

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Domain\MatchPick;

class BracketPlaySerializer extends PostBaseSerializer {
  private MatchPickSerializer $match_pick_serializer;
  private BracketSerializer $bracket_serializer;

  public function __construct($args = []) {
    $this->match_pick_serializer =
      $args['match_pick_serializer'] ?? new MatchPickSerializer();
    $this->bracket_serializer =
      $args['bracket_serializer'] ?? new BracketSerializer();
  }

  public function deserialize(array $data): BracketPlay {
    $obj_data = $this->get_object_data($data);
    return new BracketPlay($obj_data);
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
      'bracket' => [
        'serializer' => $this->bracket_serializer,
        'many' => false,
      ],
      'busted_play' => [
        'serializer' => $this,
        'many' => false,
      ],
      'picks' => [
        'serializer' => $this->match_pick_serializer,
        'many' => true,
        'required' => true,
      ],
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
      'bracket',
      'busted_play',
    ]);
  }
}
