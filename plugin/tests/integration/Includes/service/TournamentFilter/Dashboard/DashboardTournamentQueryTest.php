<?php
namespace WStrategies\BMB\tests\integration\Includes\service\TournamentFilter\Dashboard;

use WStrategies\BMB\Includes\Domain\Notification;
use WStrategies\BMB\Includes\Domain\NotificationType;
use WStrategies\BMB\Includes\Repository\NotificationRepo;
use WStrategies\BMB\Includes\Service\TournamentFilter\Dashboard\DashboardTournamentsQuery;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class DashboardTournamentQueryTest extends WPBB_UnitTestCase {
  public function test_get_live_hosted_brackets() {
    $user = $this->create_user();
    $user2 = $this->create_user();
    $live_bracket = $this->create_bracket([
      'author' => $user->ID,
      'status' => 'publish',
    ]);
    $private_bracket = $this->create_bracket([
      'author' => $user->ID,
      'status' => 'private',
    ]);
    $upcoming_bracket = $this->create_bracket([
      'author' => $user->ID,
      'status' => 'upcoming',
    ]);
    $scored_bracket = $this->create_bracket([
      'author' => $user->ID,
      'status' => 'score',
    ]);
    $non_user_bracket = $this->create_bracket([
      'author' => $user2->ID,
      'status' => 'publish',
    ]);

    wp_set_current_user($user->ID);
    $service = new DashboardTournamentsQuery();
    $brackets = $service->get_tournaments(1, 10, 'live', 'hosting');
    $count = $service->get_tournaments_count('live', 'hosting');

    $this->assertCount(2, $brackets);
    $this->assertEquals(2, $count);

    $this->assertEquals($live_bracket->id, $brackets[0]->id);
    $this->assertEquals($scored_bracket->id, $brackets[1]->id);
  }

  public function test_get_private_hosted_brackets() {
    $user = $this->create_user();
    $user2 = $this->create_user();
    $live_bracket = $this->create_bracket([
      'author' => $user->ID,
      'status' => 'publish',
    ]);
    $private_bracket = $this->create_bracket([
      'author' => $user->ID,
      'status' => 'private',
    ]);
    $upcoming_bracket = $this->create_bracket([
      'author' => $user->ID,
      'status' => 'upcoming',
    ]);
    $closed_bracket = $this->create_bracket([
      'author' => $user->ID,
      'status' => 'score',
    ]);
    $non_user_bracket = $this->create_bracket([
      'author' => $user2->ID,
      'status' => 'publish',
    ]);

    wp_set_current_user($user->ID);
    $service = new DashboardTournamentsQuery();
    $brackets = $service->get_tournaments(1, 10, 'private', 'hosting');
    $count = $service->get_tournaments_count('private', 'hosting');

    $this->assertCount(1, $brackets);
    $this->assertEquals(1, $count);

    $this->assertEquals($private_bracket->id, $brackets[0]->id);
  }

  public function test_get_upcoming_hosted_brackets() {
    $user = $this->create_user();
    $user2 = $this->create_user();
    $live_bracket = $this->create_bracket([
      'author' => $user->ID,
      'status' => 'publish',
    ]);
    $private_bracket = $this->create_bracket([
      'author' => $user->ID,
      'status' => 'private',
    ]);
    $upcoming_bracket = $this->create_bracket([
      'author' => $user->ID,
      'status' => 'upcoming',
    ]);
    $closed_bracket = $this->create_bracket([
      'author' => $user->ID,
      'status' => 'score',
    ]);
    $non_user_bracket = $this->create_bracket([
      'author' => $user2->ID,
      'status' => 'publish',
    ]);

    wp_set_current_user($user->ID);
    $service = new DashboardTournamentsQuery();
    $brackets = $service->get_tournaments(1, 10, 'upcoming', 'hosting');
    $count = $service->get_tournaments_count('upcoming', 'hosting');

    $this->assertCount(1, $brackets);
    $this->assertEquals(1, $count);

    $this->assertEquals($upcoming_bracket->id, $brackets[0]->id);
  }

  public function test_get_complete_hosted_brackets() {
    $user = $this->create_user();
    $user2 = $this->create_user();
    $live_bracket = $this->create_bracket([
      'author' => $user->ID,
      'status' => 'publish',
    ]);
    $private_bracket = $this->create_bracket([
      'author' => $user->ID,
      'status' => 'private',
    ]);
    $upcoming_bracket = $this->create_bracket([
      'author' => $user->ID,
      'status' => 'upcoming',
    ]);
    $scored_bracket = $this->create_bracket([
      'author' => $user->ID,
      'status' => 'score',
    ]);
    $complete_bracket = $this->create_bracket([
      'author' => $user->ID,
      'status' => 'complete',
    ]);
    $non_user_bracket = $this->create_bracket([
      'author' => $user2->ID,
      'status' => 'publish',
    ]);

    wp_set_current_user($user->ID);
    $service = new DashboardTournamentsQuery();

    $brackets = $service->get_tournaments(1, 10, 'complete', 'hosting');
    $count = $service->get_tournaments_count('complete', 'hosting');

    $this->assertCount(1, $brackets);
    $this->assertEquals(1, $count);

    $this->assertEquals($complete_bracket->id, $brackets[0]->id);
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
    $service = new DashboardTournamentsQuery();
    $brackets = $service->get_tournaments(1, 10, 'all', 'playing');
    $count = $service->get_tournaments_count('all', 'playing');

    $this->assertCount(4, $brackets);
    $this->assertEquals(4, $count);
  }
  public function test_get_played_tournaments_returns_single_tournament_if_mulitple_plays() {
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
    $service = new DashboardTournamentsQuery();
    $brackets = $service->get_tournaments(1, 10, 'all', 'playing');
    $count = $service->get_tournaments_count('all', 'playing');

    $this->assertCount(1, $brackets);
    $this->assertEquals(1, $count);
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
    $service = new DashboardTournamentsQuery();
    $brackets = $service->get_tournaments(1, 10, 'live', 'playing');
    $count = $service->get_tournaments_count('live', 'playing');

    $this->assertCount(2, $brackets);
    $this->assertEquals(2, $count);
  }

  public function test_get_complete_tournaments() {
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
    $service = new DashboardTournamentsQuery();
    $brackets = $service->get_tournaments(1, 10, 'complete', 'playing');
    $count = $service->get_tournaments_count('complete', 'playing');

    $this->assertCount(1, $brackets);
    $this->assertEquals(1, $count);
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
    $service = new DashboardTournamentsQuery();
    $brackets = $service->get_tournaments(1, 10, 'upcoming', 'playing');
    $count = $service->get_tournaments_count('upcoming', 'playing');

    $this->assertCount(1, $brackets);
    $this->assertEquals(1, $count);
  }
}
