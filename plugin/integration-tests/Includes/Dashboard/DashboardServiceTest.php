<?php

use WStrategies\BMB\Includes\Service\Dashboard\DashboardService;

class DashboardServiceTest extends WPBB_UnitTestCase {
  public function test_get_all_managed_brackets() {
    $user = $this->create_user();
    $user2 = $this->create_user();
    $user_bracket1 = $this->create_bracket([
      'author' => $user->ID,
      'status' => 'publish',
    ]);
    $user_bracket2 = $this->create_bracket([
      'author' => $user->ID,
      'status' => 'private',
    ]);
    $non_user_bracket = $this->create_bracket([
      'author' => $user2->ID,
      'status' => 'publish',
    ]);

    wp_set_current_user($user->ID);
    $service = new DashboardService();
    $brackets = $service->get_managed_brackets(1, 'all')['brackets'];

    $this->assertCount(2, $brackets);
    $this->assertEquals($user_bracket1->id, $brackets[0]->id);
    $this->assertEquals($user_bracket2->id, $brackets[1]->id);
  }

  public function test_get_tournaments() {
    $user = $this->create_user();
    $user2 = $this->create_user();
    $user_bracket1 = $this->create_bracket([
      'author' => $user->ID,
      'status' => 'publish',
    ]);
    $user_bracket2 = $this->create_bracket([
      'author' => $user->ID,
      'status' => 'private',
    ]);
    $non_user_bracket = $this->create_bracket([
      'author' => $user2->ID,
      'status' => 'publish',
    ]);

    wp_set_current_user($user->ID);
    $service = new DashboardService();
    $brackets = $service->get_tournaments(1, 10, 'live')['brackets'];

    $this->assertCount(2, $brackets);
    $this->assertEquals($user_bracket1->id, $brackets[0]->id);
    $this->assertEquals($user_bracket2->id, $brackets[1]->id);
  }
}
