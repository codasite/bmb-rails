<?php
namespace WStrategies\BMB\tests\integration\Includes\Hooks;

use WStrategies\BMB\Features\Bracket\BracketMetaConstants;
use WStrategies\BMB\Features\Notifications\NotificationSubscriptionRepo;
use WStrategies\BMB\Features\Notifications\NotificationType;
use WStrategies\BMB\Includes\Hooks\Loader;
use WStrategies\BMB\Includes\Hooks\UpcomingBracketHooks;
use WStrategies\BMB\Features\Bracket\UpcomingBracket\UpcomingBracketNotificationService;
use WStrategies\BMB\Includes\Utils;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class UpcomingBracketHooksTest extends WPBB_UnitTestCase {
  public function test_load() {
    $hooks = new UpcomingBracketHooks();
    $loader = $this->createMock(Loader::class);
    $loader
      ->expects($this->exactly(4))
      ->method('add_action')
      ->withConsecutive(
        ['set_object_terms', [$hooks, 'update_upcoming_status'], 10, 6],
        [
          'transition_post_status',
          [$hooks, 'transition_from_upcoming_status'],
          10,
          3,
        ],
        [
          'wp_login',
          [$hooks, 'create_upcoming_bracket_notification_on_login'],
          10,
          2,
        ],
        [
          'user_register',
          [$hooks, 'create_upcoming_bracket_notification_on_register'],
          10,
          1,
        ]
      );
    $hooks->load($loader);
  }
  public function test_add_upcoming_tag_should_change_status_to_upcoming() {
    $factory = self::factory()->bracket;
    $bracket = $factory->create_and_get([
      'status' => 'publish',
    ]);
    wp_add_post_tags($bracket->id, 'bmb_upcoming');
    $updated_bracket = $factory->get_object_by_id($bracket->id);
    $this->assertEquals('upcoming', $updated_bracket->status);
  }
  public function test_status_remove_upcoming_tag_should_change_status_to_publish() {
    $factory = self::factory()->bracket;
    $bracket = $factory->create_and_get([
      'status' => 'upcoming',
    ]);
    // remove the upcoming tag
    wp_set_post_terms($bracket->id, '', 'post_tag');
    $updated_bracket = $factory->get_object_by_id($bracket->id);
    $this->assertEquals('publish', $updated_bracket->status);
  }

  public function test_add_upcoming_tag_resets_notify_flag() {
    $bracket = $this->create_bracket([
      'status' => 'publish',
    ]);
    // update meta
    update_post_meta(
      $bracket->id,
      BracketMetaConstants::UPCOMING_NOTIFICATION_SENT,
      true
    );
    // add the upcoming tag
    wp_add_post_tags($bracket->id, 'bmb_upcoming');
    $this->assertEquals(
      false,
      get_post_meta(
        $bracket->id,
        BracketMetaConstants::UPCOMING_NOTIFICATION_SENT,
        true
      )
    );
  }

  public function test_upcoming_notifications_are_sent() {
    $bracket = $this->create_bracket([
      'status' => 'upcoming',
    ]);
    $notification_service_mock = $this->createMock(
      UpcomingBracketNotificationService::class
    );
    $notification_service_mock
      ->expects($this->once())
      ->method('notify_upcoming_bracket_live')
      ->with($bracket->id);

    $bracket_hooks = new UpcomingBracketHooks([
      'notification_service' => $notification_service_mock,
    ]);
    $bracket_hooks->transition_from_upcoming_status(
      'publish',
      'upcoming',
      get_post($bracket->id)
    );
    $this->assertEquals(
      true,
      get_post_meta(
        $bracket->id,
        BracketMetaConstants::UPCOMING_NOTIFICATION_SENT,
        true
      )
    );
  }

  public function test_upcoming_notifications_are_not_sent() {
    $bracket = $this->create_bracket([
      'status' => 'upcoming',
    ]);
    // update post meta
    update_post_meta(
      $bracket->id,
      BracketMetaConstants::UPCOMING_NOTIFICATION_SENT,
      true
    );
    $notification_service_mock = $this->createMock(
      UpcomingBracketNotificationService::class
    );
    $notification_service_mock
      ->expects($this->never())
      ->method('notify_upcoming_bracket_live');

    $bracket_hooks = new UpcomingBracketHooks([
      'notification_service' => $notification_service_mock,
    ]);
    $bracket_hooks->transition_from_upcoming_status(
      'publish',
      'upcoming',
      get_post($bracket->id)
    );
  }

  public function test_create_upcoming_bracket_notification_on_login() {
    $bracket = $this->create_bracket();
    $user = self::factory()->user->create_and_get();
    $user_id = $user->ID;

    $utils_mock = $this->createMock(Utils::class);
    $utils_mock
      ->expects($this->once())
      ->method('pop_cookie')
      ->with($this->equalTo('wpbb_upcoming_bracket_id'))
      ->willReturn($bracket->id);
    $hooks = new UpcomingBracketHooks([
      'utils' => $utils_mock,
    ]);
    $hooks->create_upcoming_bracket_notification_on_login('test_login', $user);

    $notification_sub_repo = new NotificationSubscriptionRepo();
    $notifications = $notification_sub_repo->get([
      'user_id' => $user_id,
      'bracket_id' => $bracket->id,
    ]);

    $this->assertEquals(1, count($notifications));

    $notification = $notifications[0];

    $this->assertEquals($user_id, $notification->user_id);
    $this->assertEquals($bracket->id, $notification->post_id);
    $this->assertEquals(
      NotificationType::BRACKET_UPCOMING,
      $notification->notification_type
    );
  }

  public function test_create_upcoming_bracket_notification_on_register() {
    $bracket = $this->create_bracket();
    $user = self::factory()->user->create_and_get();
    $user_id = $user->ID;

    $utils_mock = $this->createMock(Utils::class);
    $utils_mock
      ->expects($this->once())
      ->method('pop_cookie')
      ->with($this->equalTo('wpbb_upcoming_bracket_id'))
      ->willReturn($bracket->id);
    $hooks = new UpcomingBracketHooks([
      'utils' => $utils_mock,
    ]);
    $hooks->create_upcoming_bracket_notification_on_register($user_id);

    $notification_sub_repo = new NotificationSubscriptionRepo();
    $notifications = $notification_sub_repo->get([
      'user_id' => $user_id,
      'bracket_id' => $bracket->id,
    ]);

    $this->assertEquals(1, count($notifications));

    $notification = $notifications[0];

    $this->assertEquals($user_id, $notification->user_id);
    $this->assertEquals($bracket->id, $notification->post_id);
    $this->assertEquals(
      NotificationType::BRACKET_UPCOMING,
      $notification->notification_type
    );
  }

  public function test_notification_is_not_created_when_bracket_does_not_exist() {
    $user = self::factory()->user->create_and_get();
    $user_id = $user->ID;

    $utils_mock = $this->createMock(Utils::class);
    $utils_mock
      ->expects($this->once())
      ->method('pop_cookie')
      ->with($this->equalTo('wpbb_upcoming_bracket_id'))
      ->willReturn(999);
    $hooks = new UpcomingBracketHooks([
      'utils' => $utils_mock,
    ]);
    $hooks->create_upcoming_bracket_notification($user_id);

    $notification_repo_mock = $this->createMock(
      NotificationSubscriptionRepo::class
    );
    $notification_repo_mock->expects($this->never())->method('add');
  }

  public function test_status_should_not_change_to_published_if_updated_and_has_upcoming_tag() {
    $factory = self::factory()->bracket;
    $bracket = $factory->create_and_get([
      'status' => 'publish',
    ]);
    wp_add_post_tags($bracket->id, 'bmb_upcoming');
    $updated_bracket = $factory->get_object_by_id($bracket->id);
    $this->assertEquals('upcoming', $updated_bracket->status);
    wp_update_post([
      'ID' => $bracket->id,
      'post_status' => 'publish',
    ]);
    $updated_bracket = $factory->get_object_by_id($bracket->id);
    $this->assertEquals('upcoming', $updated_bracket->status);
  }
}
