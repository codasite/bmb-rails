<?php

namespace WStrategies\BMB\Includes\Service\Serializer;

use WStrategies\BMB\Features\Bracket\BracketMetaConstants;
use WStrategies\BMB\Includes\Domain\Bracket;

class BracketSerializer extends PostBaseSerializer {
  public function deserialize(array|string $data): Bracket {
    $obj_data = $this->get_object_data($data);
    return new Bracket($obj_data);
  }
  public function get_serialized_fields(): array {
    return array_merge(parent::get_serialized_fields(), [
      'num_teams' => [
        'required' => true,
      ],
      'wildcard_placement' => [
        'required' => true,
      ],
      'month',
      'year',
      'matches' => new BracketMatchSerializer([
        'many' => true,
        'required' => true,
      ]),
      'results' => new PickSerializer([
        'many' => true,
      ]),
      'most_popular_picks' => new PickSerializer([
        'many' => true,
      ]),
      'title' => [
        'required' => true,
      ],
      'is_open',
      'is_printable',
      'fee',
      BracketMetaConstants::SHOULD_NOTIFY_RESULTS_UPDATED,
      'is_voting',
      'live_round_index',
      'is_template',
    ]);
  }
}
