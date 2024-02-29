<?php

use Spatie\Snapshots\MatchesSnapshots;
use WStrategies\BMB\Includes\Domain\BracketMatch;
use WStrategies\BMB\Includes\Domain\MatchPick;
use WStrategies\BMB\Includes\Domain\Team;
use WStrategies\BMB\Includes\Factory\MatchPickResultFactory;
use WStrategies\BMB\Includes\Service\BracketMatchService;

class MatchPickResultFactoryTest extends WPBB_UnitTestCase {
  use MatchesSnapshots;
  public function test_create_match_pick_results() {
    $bracket = $this->create_bracket([
      'status' => 'publish',
      'num_teams' => 4,
      'matches' => [
        new BracketMatch([
          'id' => 1,
          'round_index' => 0,
          'match_index' => 0,
          'team1' => new Team([
            'name' => 'Team 1',
            'id' => 1,
          ]),
          'team2' => new Team([
            'name' => 'Team 2',
            'id' => 2,
          ]),
        ]),
        new BracketMatch([
          'round_index' => 0,
          'match_index' => 1,
          'team1' => new Team([
            'name' => 'Team 3',
            'id' => 3,
          ]),
          'team2' => new Team([
            'name' => 'Team 4',
            'id' => 4,
          ]),
        ]),
      ],
    ]);
    $bracket = $this->update_bracket($bracket, [
      'results' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ],
        [
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[1]->team2->id,
        ],
        [
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[1]->team2->id,
        ],
      ],
    ]);

    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'picks' => [
        new MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
        new MatchPick([
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[1]->team2->id,
        ]),
        new MatchPick([
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);
    $match_service = new BracketMatchService();
    $matches = $match_service->matches_from_picks(
      $bracket->matches,
      $bracket->results
    );
    $factory = new MatchPickResultFactory();
    $results = $factory->create_match_pick_results($matches, $play->picks);
    $this->assertMatchesJsonSnapshot($results);
  }
}
