<?php

namespace WStrategies\BMB\Includes\Factory;
use WStrategies\BMB\Includes\Domain\Bracket;

class BracketFactory extends PostBaseFactory {
  private BracketMatchFactory $match_factory;
  private MatchPickFactory $results_factory;

  public function __construct(array $args) {
    $team_factory = $args['team_factory'] ?? new TeamFactory();
    $this->match_factory =
      $args['match_factory'] ??
      new BracketMatchFactory(['team_factory' => $team_factory]);
    $this->results_factory =
      $args['results_factory'] ??
      new MatchPickFactory(['team_factory' => $team_factory]);
  }
  public function create(array $data): Bracket {
    $bracket = new Bracket();
    $this->initialize($bracket, $data);

    $bracket->month = $data['month'] ?? null;
    $bracket->year = $data['year'] ?? null;
    $bracket->num_teams = (int) ($data['num_teams'] ?? null);
    $bracket->wildcard_placement = (int) ($data['wildcard_placement'] ?? null);
    $bracket->matches = $data['matches'] ?? [];
    $bracket->results = $data['results'] ?? [];
    $bracket->results_first_updated_at =
      $data['results_first_updated_at'] ?? null;

    return $bracket;
  }
}
