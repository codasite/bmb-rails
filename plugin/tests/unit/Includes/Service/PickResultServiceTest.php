<?php

use Spatie\Snapshots\MatchesSnapshots;
use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Includes\Domain\Fakes\PickResultFakeFactory;
use WStrategies\BMB\Includes\Service\PickResultService;

class PickResultServiceTest extends TestCase {
  use MatchesSnapshots;
  public function test_get_winning_team_map_should_return_map_of_team_id_to_most_recent_match_won() {
    $results = [
      // PickResultFakeFactory::create_pick_result(1, 2, 1, 1, 0, 0),
      // PickResultFakeFactory::create_pick_result(3, 4, 3, 3, 0, 1),
      // PickResultFakeFactory::create_pick_result(5, 6, 5, 5, 0, 2),
      // PickResultFakeFactory::create_pick_result(7, 8, 8, 7, 0, 3),
      // PickResultFakeFactory::create_pick_result(1, 3, 1, 1, 1, 0),
      // PickResultFakeFactory::create_pick_result(5, 8, 5, 5, 1, 1),
      // PickResultFakeFactory::create_pick_result(1, 5, 5, 5, 2, 0),
      PickResultFakeFactory::create_pick_result([
        'team1_id' => 1,
        'team2_id' => 2,
        'winning_team_id' => 1,
        'picked_team_id' => 1,
        'round_index' => 0,
        'match_index' => 0,
      ]),
      PickResultFakeFactory::create_pick_result([
        'team1_id' => 3,
        'team2_id' => 4,
        'winning_team_id' => 3,
        'picked_team_id' => 3,
        'round_index' => 0,
        'match_index' => 1,
      ]),
      PickResultFakeFactory::create_pick_result([
        'team1_id' => 5,
        'team2_id' => 6,
        'winning_team_id' => 5,
        'picked_team_id' => 5,
        'round_index' => 0,
        'match_index' => 2,
      ]),
      PickResultFakeFactory::create_pick_result([
        'team1_id' => 7,
        'team2_id' => 8,
        'winning_team_id' => 8,
        'picked_team_id' => 7,
        'round_index' => 0,
        'match_index' => 3,
      ]),
      PickResultFakeFactory::create_pick_result([
        'team1_id' => 1,
        'team2_id' => 3,
        'winning_team_id' => 1,
        'picked_team_id' => 1,
        'round_index' => 1,
        'match_index' => 0,
      ]),
      PickResultFakeFactory::create_pick_result([
        'team1_id' => 5,
        'team2_id' => 8,
        'winning_team_id' => 5,
        'picked_team_id' => 5,
        'round_index' => 1,
        'match_index' => 1,
      ]),
      PickResultFakeFactory::create_pick_result([
        'team1_id' => 1,
        'team2_id' => 5,
        'winning_team_id' => 5,
        'picked_team_id' => 5,
        'round_index' => 2,
        'match_index' => 0,
      ]),
    ];

    $service = new PickResultService();
    $this->assertMatchesJsonSnapshot($service->get_winning_team_map($results));
  }

  public function test_get_losing_team_map_should_return_map_of_team_id_to_most_recent_match_lost() {
    $results = [
      PickResultFakeFactory::create_pick_result(1, 2, 1, 1, 0, 0),
      PickResultFakeFactory::create_pick_result(3, 4, 3, 3, 0, 1),
      PickResultFakeFactory::create_pick_result(5, 6, 5, 5, 0, 2),
      PickResultFakeFactory::create_pick_result(7, 8, 8, 7, 0, 3),
      PickResultFakeFactory::create_pick_result(1, 3, 1, 1, 1, 0),
      PickResultFakeFactory::create_pick_result(5, 8, 5, 5, 1, 1),
      PickResultFakeFactory::create_pick_result(1, 5, 5, 5, 2, 0),
    ];

    $service = new PickResultService();
    $this->assertMatchesJsonSnapshot($service->get_losing_team_map($results));
  }
}
