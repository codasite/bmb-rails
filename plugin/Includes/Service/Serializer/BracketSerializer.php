<?php

namespace WStrategies\BMB\Includes\Service\Serializer;

use WStrategies\BMB\Includes\Domain\Bracket;

class BracketSerializer extends PostBaseSerializer {
  private BracketMatchSerializer $match_serializer;
  private PickSerializer $results_serializer;

  public function __construct($args = []) {
    $this->match_serializer =
      $args['match_serializer'] ?? new BracketMatchSerializer();
    $this->results_serializer =
      $args['results_serializer'] ?? new PickSerializer();
  }
  public function deserialize(array $data): Bracket {
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
      'matches' => [
        'serializer' => $this->match_serializer,
        'many' => true,
        'required' => true,
      ],
      'results' => [
        'serializer' => $this->results_serializer,
        'many' => true,
      ],
      'most_popular_picks' => [
        'serializer' => $this->results_serializer,
        'many' => true,
      ],
      'title' => [
        'required' => true,
      ],
      'is_open',
      'is_printable',
      'fee',
      'should_notify_results_updated',
    ]);
  }
}
