<?php

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Hooks\UpcomingBracketHooks;
use WStrategies\BMB\Includes\Loader;
use WStrategies\BMB\Includes\Service\Notifications\UpcomingBracketNotificationService;

class UpcomingBracketHooksTest extends WPBB_UnitTestCase {
  public function test_load() {
    $hooks = new UpcomingBracketHooks();
    $loader = $this->createMock(Loader::class);
    $loader
      ->expects($this->exactly(2))
      ->method('add_action')
      ->withConsecutive(
        ['set_object_terms', [$hooks, 'update_upcoming_status'], 10, 6],
        [
          'transition_post_status',
          [$hooks, 'transition_from_upcoming_status'],
          10,
          3,
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
    $bracket = self::factory()->bracket->create_and_get([
      'status' => 'publish',
    ]);
    // update meta
    update_post_meta(
      $bracket->id,
      UpcomingBracketHooks::UPCOMING_NOTIFICATION_SENT_META_KEY,
      true
    );
    // add the upcoming tag
    wp_add_post_tags($bracket->id, 'bmb_upcoming');
    $this->assertEquals(
      false,
      get_post_meta(
        $bracket->id,
        UpcomingBracketHooks::UPCOMING_NOTIFICATION_SENT_META_KEY,
        true
      )
    );
  }

  public function test_upcoming_notifications_are_sent() {
    $bracket = self::factory()->bracket->create_and_get([
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
        UpcomingBracketHooks::UPCOMING_NOTIFICATION_SENT_META_KEY,
        true
      )
    );
  }

  public function test_upcoming_notifications_are_not_sent() {
    $bracket = self::factory()->bracket->create_and_get([
      'status' => 'upcoming',
    ]);
    // update post meta
    update_post_meta(
      $bracket->id,
      UpcomingBracketHooks::UPCOMING_NOTIFICATION_SENT_META_KEY,
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
}
