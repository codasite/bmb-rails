<?php

use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Includes\Domain\Fakes\PickResultFakeFactory;

class PickResultServiceTest extends TestCase {
  public function test_get_winning_team_map_should_return_map_of_winning_teams_to_id() {
    $results = [
      PickResultFakeFactory::get_correct_pick_result(),
      PickResultFakeFactory::get_incorrect_pick_result(),
    ];
  }
}
