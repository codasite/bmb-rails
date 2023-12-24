<?php

namespace WStrategies\BMB\Includes\Service\Serializer;

use WStrategies\BMB\Includes\Domain\Bracket;

class BracketSerializer extends PostBaseSerializer {
  private $match_serializer;
  private $results_serializer;
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
      // 'results_first_updated_at' => [
      //   'value' => [$this, 'serialize_results_first_updated_at'],
      // ],
    ]);
  }
  public function serialize_results_first_updated_at(
    Bracket $bracket
  ): ?string {
    return $bracket->results_first_updated_at
      ? $bracket->results_first_updated_at->format('Y-m-d H:i:s')
      : null;
  }
}
