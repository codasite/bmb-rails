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
    $this->assertCount(8, $mapped_results);
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

  public function test_should_return_a_mapping_of_team_id_to_the_pick_result_of_the_most_recent_match_played_given_partial_results() {
    $results = [
      PickResultFakeFactory::create_pick_result(1, 3, 1, 1, 1, 0),
      PickResultFakeFactory::create_pick_result(5, 8, 5, 5, 1, 1),
      PickResultFakeFactory::create_pick_result(1, 5, 5, 5, 2, 0),
    ];

    $service = new PickResultService();
    $mapped_results = $service->get_most_recent_pick_result_map($results);
    $this->assertCount(4, $mapped_results);
    // Assert every team id is represented in the map
    $keys = array_keys($mapped_results);
    $this->assertContains(1, $keys);
    $this->assertContains(3, $keys);
    $this->assertContains(5, $keys);
    $this->assertContains(8, $keys);

    // Assert team 1 and 5 point to the most recent match played
    $this->assertEquals($mapped_results[1], $results[2]);
    $this->assertEquals($mapped_results[5], $results[2]);

    $this->assertMatchesJsonSnapshot($mapped_results);
  }

  public function test_should_return_result_for_first_ranked_team_when_played_and_won() {
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
    $result = $service->get_pick_result_for_many_teams($results, [5, 1, 7, 3]);
    $this->assertEquals($results[6], $result);
  }
  public function test_should_return_result_for_first_ranked_team_when_played_and_lost() {
    $results = [
      PickResultFakeFactory::create_pick_result(1, 2, 1, 1, 0, 0),
      PickResultFakeFactory::create_pick_result(3, 4, 3, 3, 0, 1),
      PickResultFakeFactory::create_pick_result(5, 6, 5, 5, 0, 2),
      PickResultFakeFactory::create_pick_result(7, 8, 8, 7, 0, 3),
      PickResultFakeFactory::create_pick_result(1, 3, 1, 1, 1, 0),
      PickResultFakeFactory::create_pick_result(5, 8, 5, 5, 1, 1),
      PickResultFakeFactory::create_pick_result(1, 5, 5, 1, 2, 0),
    ];

    $service = new PickResultService();
    $result = $service->get_pick_result_for_many_teams($results, [1, 5, 7, 3]);
    $this->assertEquals($result, $results[6]);
  }
  public function test_should_return_result_for_second_ranked_team_when_first_ranked_team_did_not_play() {
    $results = [
      PickResultFakeFactory::create_pick_result(1, 3, 1, 1, 1, 0),
      PickResultFakeFactory::create_pick_result(5, 8, 5, 7, 1, 1),
      PickResultFakeFactory::create_pick_result(1, 5, 5, 7, 2, 0),
    ];

    $service = new PickResultService();
    $result = $service->get_pick_result_for_many_teams($results, [7, 1, 5, 3]);
    $this->assertEquals($results[0], $result);
  }

  public function test_should_return_result_for_third_ranked_team_when_first_or_second_ranked_team_did_not_play() {
    $results = [
      // Ignore theses matches from first round
      // PickResultFakeFactory::create_pick_result(1, 2, 1, 2, 0, 0),
      // PickResultFakeFactory::create_pick_result(3, 4, 3, 3, 0, 1),
      // PickResultFakeFactory::create_pick_result(7, 8, 8, 7, 0, 3),

      PickResultFakeFactory::create_pick_result(5, 6, 6, 6, 0, 2),
      PickResultFakeFactory::create_pick_result(1, 3, 1, 2, 1, 0),
      PickResultFakeFactory::create_pick_result(6, 8, 8, 7, 1, 1),
      PickResultFakeFactory::create_pick_result(1, 8, 8, 7, 2, 0),
    ];

    $service = new PickResultService();
    $result = $service->get_pick_result_for_many_teams($results, [7, 2, 6, 3]);
    $this->assertEquals($results[0], $result);
  }
  public function test_should_not_return_result_for_team_that_played_but_was_not_picked() {
    $results = [
      // Ignore first rounds
      // PickResultFakeFactory::create_pick_result(1, 2, 1, 2, 0, 0),
      // PickResultFakeFactory::create_pick_result(3, 4, 3, 3, 0, 1),
      // PickResultFakeFactory::create_pick_result(5, 6, 6, 6, 0, 2),
      // PickResultFakeFactory::create_pick_result(7, 8, 8, 7, 0, 3),

      PickResultFakeFactory::create_pick_result(1, 3, 1, 2, 1, 0),
      PickResultFakeFactory::create_pick_result(6, 8, 8, 7, 1, 1),
      PickResultFakeFactory::create_pick_result(1, 8, 8, 7, 2, 0),
    ];

    $service = new PickResultService();
    $result = $service->get_pick_result_for_many_teams($results, [7, 2, 6, 3]);
    $this->assertNull($result);
  }
}
