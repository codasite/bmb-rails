<?php

use Spatie\Snapshots\MatchesSnapshots;
use WStrategies\BMB\Includes\Service\BracketMatchService;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class BracketMatchServiceTest extends WPBB_UnitTestCase {
  use MatchesSnapshots;
  public function test_matches_2d_from_picks() {
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    $updated = $this->update_bracket($bracket->id, [
      'results' => [
        [
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ],
        [
          'round_index' => 0,
          'match_index' => 1,
          'winning_team_id' => $bracket->matches[1]->team1->id,
        ],
        [
          'round_index' => 1,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ],
      ],
    ]);

    $service = new BracketMatchService();
    $matches = $service->matches_2d_from_picks(
      $bracket->matches,
      $updated->results
    );
    $this->assertCount(2, $matches[0]);
    $this->assertCount(1, $matches[1]);
    $this->assertTrue($matches[0][0]->has_results());
    $this->assertTrue($matches[0][1]->has_results());
    $this->assertTrue($matches[1][0]->has_results());
  }
}
