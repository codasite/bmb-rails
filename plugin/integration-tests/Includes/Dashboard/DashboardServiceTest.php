<?php

use WStrategies\BMB\Includes\Domain\Notification;
use WStrategies\BMB\Includes\Domain\NotificationType;
use WStrategies\BMB\Includes\Repository\NotificationRepo;
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

  public function test_get_all_tournaments() {
    $user = $this->create_user();
    $user2 = $this->create_user();
    $user_tourney = $this->create_bracket([
      'author' => $user->ID,
      'status' => 'publish',
    ]);
    $another_user_tourney = $this->create_bracket([
      'author' => $user2->ID,
      'status' => 'publish',
    ]);
    $non_played_tourney = $this->create_bracket([
      'author' => $user2->ID,
      'status' => 'publish',
    ]);
    $scored_tourney = $this->create_bracket([
      'author' => $user2->ID,
      'status' => 'score',
    ]);
    $complete_tourney = $this->create_bracket([
      'author' => $user2->ID,
      'status' => 'complete',
    ]);
    $private_tourney = $this->create_bracket([
      'author' => $user2->ID,
      'status' => 'private',
    ]);
    $play1 = $this->create_play([
      'bracket_id' => $user_tourney->id,
      'author' => $user->ID,
    ]);
    $play2 = $this->create_play([
      'bracket_id' => $another_user_tourney->id,
      'author' => $user->ID,
    ]);
    $play3 = $this->create_play([
      'bracket_id' => $scored_tourney->id,
      'author' => $user->ID,
    ]);
    $play4 = $this->create_play([
      'bracket_id' => $complete_tourney->id,
      'author' => $user->ID,
    ]);

    wp_set_current_user($user->ID);
    $service = new DashboardService();
    $brackets = $service->get_tournaments(1, 10, 'all')['brackets'];

    $this->assertCount(4, $brackets);
  }
  public function test_get_tournaments_returns_single_tournament_if_mulitple_plays() {
    $user = $this->create_user();
    $user_tourney = $this->create_bracket([
      'author' => $user->ID,
      'status' => 'publish',
    ]);
    $play1 = $this->create_play([
      'bracket_id' => $user_tourney->id,
      'author' => $user->ID,
    ]);
    $play2 = $this->create_play([
      'bracket_id' => $user_tourney->id,
      'author' => $user->ID,
    ]);

    wp_set_current_user($user->ID);
    $service = new DashboardService();
    $brackets = $service->get_tournaments(1, 10, 'all')['brackets'];

    $this->assertCount(1, $brackets);
  }

  public function test_get_live_tournaments() {
    $user = $this->create_user();
    $user2 = $this->create_user();
    $live_tourney = $this->create_bracket([
      'author' => $user2->ID,
      'status' => 'publish',
    ]);
    $scored_tourney = $this->create_bracket([
      'author' => $user2->ID,
      'status' => 'score',
    ]);
    $complete_tourney = $this->create_bracket([
      'author' => $user2->ID,
      'status' => 'complete',
    ]);
    $play1 = $this->create_play([
      'bracket_id' => $live_tourney->id,
      'author' => $user->ID,
    ]);
    $play4 = $this->create_play([
      'bracket_id' => $scored_tourney->id,
      'author' => $user->ID,
    ]);
    $play5 = $this->create_play([
      'bracket_id' => $complete_tourney->id,
      'author' => $user->ID,
    ]);

    wp_set_current_user($user->ID);
    $service = new DashboardService();
    $brackets = $service->get_tournaments(1, 10, 'live')['brackets'];

    $this->assertCount(1, $brackets);
  }

  public function test_get_closed_tournaments() {
    $user = $this->create_user();
    $user2 = $this->create_user();
    $live_tourney = $this->create_bracket([
      'author' => $user2->ID,
      'status' => 'publish',
    ]);
    $scored_tourney = $this->create_bracket([
      'author' => $user2->ID,
      'status' => 'score',
    ]);
    $complete_tourney = $this->create_bracket([
      'author' => $user2->ID,
      'status' => 'complete',
    ]);
    $play1 = $this->create_play([
      'bracket_id' => $live_tourney->id,
      'author' => $user->ID,
    ]);
    $play4 = $this->create_play([
      'bracket_id' => $scored_tourney->id,
      'author' => $user->ID,
    ]);
    $play5 = $this->create_play([
      'bracket_id' => $complete_tourney->id,
      'author' => $user->ID,
    ]);

    wp_set_current_user($user->ID);
    $service = new DashboardService();
    $brackets = $service->get_tournaments(1, 10, 'closed')['brackets'];

    $this->assertCount(2, $brackets);
  }

  public function test_get_upcoming_tournaments() {
    $user = $this->create_user();
    $user2 = $this->create_user();
    $upcoming_tourney = $this->create_bracket([
      'author' => $user2->ID,
      'status' => 'upcoming',
    ]);
    $live_tourney = $this->create_bracket([
      'author' => $user2->ID,
      'status' => 'publish',
    ]);
    $scored_tourney = $this->create_bracket([
      'author' => $user2->ID,
      'status' => 'score',
    ]);
    $complete_tourney = $this->create_bracket([
      'author' => $user2->ID,
      'status' => 'complete',
    ]);
    $play1 = $this->create_play([
      'bracket_id' => $live_tourney->id,
      'author' => $user->ID,
    ]);
    $play4 = $this->create_play([
      'bracket_id' => $scored_tourney->id,
      'author' => $user->ID,
    ]);
    $play5 = $this->create_play([
      'bracket_id' => $complete_tourney->id,
      'author' => $user->ID,
    ]);
    $notification_repo = new NotificationRepo();
    $notification = new Notification([
      'user_id' => $user->ID,
      'post_id' => $upcoming_tourney->id,
      'notification_type' => NotificationType::BRACKET_UPCOMING,
    ]);
    $notification_repo->add($notification);

    wp_set_current_user($user->ID);
    $service = new DashboardService();
    $brackets = $service->get_tournaments(1, 10, 'upcoming')['brackets'];

    $this->assertCount(1, $brackets);
  }
}
