<?php

namespace WStrategies\BMB\Includes\Service\Serializer;

use WStrategies\BMB\Includes\Domain\Bracket;

class BracketSerializer extends PostBaseSerializer {
  private BracketMatchSerializer $match_serializer;
  private MatchPickSerializer $results_serializer;

  public function __construct($args = []) {
    $this->match_serializer =
      $args['match_serializer'] ?? new BracketMatchSerializer();
    $this->results_serializer =
      $args['results_serializer'] ?? new MatchPickSerializer();
  }
  public function get_serialized_fields(): array {
    return array_merge(parent::get_serialized_fields(), [
      'num_teams',
      'wildcard_placement',
      'month',
      'year',
      'matches' => [
        'serializer' => $this->match_serializer,
        'many' => true,
      ],
      'results' => [
        'serializer' => $this->results_serializer,
        'many' => true,
      ],
    ]);
  }
}
