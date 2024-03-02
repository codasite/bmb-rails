<?php

use Spatie\Snapshots\MatchesSnapshots;
use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Includes\Domain\Fakes\PickResultFakeFactory;
use WStrategies\BMB\Includes\Service\PickResultService;

class PickResultServiceTest extends TestCase {
  use MatchesSnapshots;
  public function test_should_return_a_mapping_of_team_id_to_the_pick_result_of_the_most_recent_match_played() {
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
    $mapped_results = $service->get_most_recent_pick_result_map($results);
    // Assert every team id is represented in the map
    $keys = array_keys($mapped_results);
    $this->assertContains(1, $keys);
    $this->assertContains(2, $keys);
    $this->assertContains(3, $keys);
    $this->assertContains(4, $keys);
    $this->assertContains(5, $keys);
    $this->assertContains(6, $keys);
    $this->assertContains(7, $keys);
    $this->assertContains(8, $keys);

    // Assert team 1 and 5 point to the most recent match played
    $this->assertEquals($mapped_results[1], $results[6]);
    $this->assertEquals($mapped_results[5], $results[6]);

    $this->assertMatchesJsonSnapshot($mapped_results);
  }

  public function test_should_return_the_most_recent_match_played_when_team_played() {
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
    $result = $service->get_pick_result_for_single_team($results, 3);
    $this->assertEquals($result, $results[4]);
  }

  public function test_should_return_null_when_team_did_not_play() {
    $results = [
      PickResultFakeFactory::create_pick_result(1, 3, 1, 1, 1, 0),
      PickResultFakeFactory::create_pick_result(5, 8, 5, 5, 1, 1),
      PickResultFakeFactory::create_pick_result(1, 5, 5, 5, 2, 0),
    ];

    $service = new PickResultService();
    $result = $service->get_pick_result_for_single_team($results, 2);
    $this->assertNull($result);
  }
}
