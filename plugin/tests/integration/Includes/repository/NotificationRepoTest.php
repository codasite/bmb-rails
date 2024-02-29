<?php

use WStrategies\BMB\Includes\Domain\Notification;
use WStrategies\BMB\Includes\Domain\NotificationType;
use WStrategies\BMB\Includes\Repository\NotificationRepo;

class NotificationRepoTest extends WPBB_UnitTestCase {
  private NotificationRepo $notification_repo;

  public function set_up(): void {
    parent::set_up();

    $this->notification_repo = new NotificationRepo();
  }

  public function test_add() {
    $user = self::factory()->user->create_and_get();
    $post = $this->create_post();
    $notification = $this->notification_repo->add(
      new Notification([
        'user_id' => $user->ID,
        'post_id' => $post->ID,
        'notification_type' => NotificationType::BRACKET_UPCOMING,
      ])
    );
    $this->assertIsInt($notification->id);
    $this->assertSame($user->ID, $notification->user_id);
    $this->assertSame($post->ID, $notification->post_id);
    $this->assertSame(
      NotificationType::BRACKET_UPCOMING,
      $notification->notification_type
    );
  }

  public function test_get_single() {
    $user = self::factory()->user->create_and_get();
    $post = $this->create_post();
    $notification = $this->notification_repo->add(
      new Notification([
        'user_id' => $user->ID,
        'post_id' => $post->ID,
        'notification_type' => NotificationType::BRACKET_UPCOMING,
      ])
    );
    $found_notification = $this->notification_repo->get([
      'id' => $notification->id,
      'single' => true,
    ]);
    $this->assertInstanceOf(Notification::class, $found_notification);
    $this->assertSame($notification->id, $found_notification->id);
    $this->assertSame($user->ID, $found_notification->user_id);
    $this->assertSame($post->ID, $found_notification->post_id);
    $this->assertSame(
      NotificationType::BRACKET_UPCOMING,
      $found_notification->notification_type
    );
  }

  public function test_get_by_user() {
    $user1 = self::factory()->user->create_and_get();
    $user2 = self::factory()->user->create_and_get();
    $post = $this->create_post();
    $notification1 = $this->notification_repo->add(
      new Notification([
        'user_id' => $user1->ID,
        'post_id' => $post->ID,
        'notification_type' => NotificationType::BRACKET_UPCOMING,
      ])
    );
    $notification2 = $this->notification_repo->add(
      new Notification([
        'user_id' => $user2->ID,
        'post_id' => $post->ID,
        'notification_type' => NotificationType::BRACKET_UPCOMING,
      ])
    );
    $found_notifications = $this->notification_repo->get([
      'user_id' => $user1->ID,
    ]);
    $this->assertCount(1, $found_notifications);
    $this->assertInstanceOf(Notification::class, $found_notifications[0]);
    $this->assertSame($notification1->id, $found_notifications[0]->id);
    $this->assertSame($user1->ID, $found_notifications[0]->user_id);
    $this->assertSame($post->ID, $found_notifications[0]->post_id);
    $this->assertSame(
      NotificationType::BRACKET_UPCOMING,
      $found_notifications[0]->notification_type
    );
  }
  public function test_get_by_post_id() {
    $user = self::factory()->user->create_and_get();
    $post1 = $this->create_post();
    $post2 = $this->create_post();
    $notification1 = $this->notification_repo->add(
      new Notification([
        'user_id' => $user->ID,
        'post_id' => $post1->ID,
        'notification_type' => NotificationType::BRACKET_UPCOMING,
      ])
    );
    $notification2 = $this->notification_repo->add(
      new Notification([
        'user_id' => $user->ID,
        'post_id' => $post2->ID,
        'notification_type' => NotificationType::BRACKET_UPCOMING,
      ])
    );
    $found_notifications = $this->notification_repo->get_by_post_id(
      $post1->ID,
      NotificationType::BRACKET_UPCOMING
    );
    $this->assertCount(1, $found_notifications);
    $this->assertInstanceOf(Notification::class, $found_notifications[0]);
    $this->assertSame($notification1->id, $found_notifications[0]->id);
    $this->assertSame($user->ID, $found_notifications[0]->user_id);
    $this->assertSame($post1->ID, $found_notifications[0]->post_id);
    $this->assertSame(
      NotificationType::BRACKET_UPCOMING,
      $found_notifications[0]->notification_type
    );
  }
  public function test_get_by_user_notification_type_and_post() {
    $user1 = self::factory()->user->create_and_get();
    $user2 = self::factory()->user->create_and_get();
    $post1 = $this->create_post();
    $post2 = $this->create_post();
    $notification1 = $this->notification_repo->add(
      new Notification([
        'user_id' => $user1->ID,
        'post_id' => $post1->ID,
        'notification_type' => NotificationType::BRACKET_UPCOMING,
      ])
    );
    $notification2 = $this->notification_repo->add(
      new Notification([
        'user_id' => $user1->ID,
        'post_id' => $post2->ID,
        'notification_type' => NotificationType::BRACKET_UPCOMING,
      ])
    );
    $notification3 = $this->notification_repo->add(
      new Notification([
        'user_id' => $user2->ID,
        'post_id' => $post2->ID,
        'notification_type' => NotificationType::BRACKET_UPCOMING,
      ])
    );
    $found_notifications = $this->notification_repo->get([
      'user_id' => $user1->ID,
      'notification_type' => NotificationType::BRACKET_UPCOMING,
      'post_id' => $post1->ID,
    ]);
    $this->assertCount(1, $found_notifications);
    $this->assertInstanceOf(Notification::class, $found_notifications[0]);
    $this->assertSame($notification1->id, $found_notifications[0]->id);
    $this->assertSame($user1->ID, $found_notifications[0]->user_id);
    $this->assertSame($post1->ID, $found_notifications[0]->post_id);
    $this->assertSame(
      NotificationType::BRACKET_UPCOMING,
      $found_notifications[0]->notification_type
    );
  }

  public function test_get_by_post_and_type() {
    $user1 = self::factory()->user->create_and_get();
    $user2 = self::factory()->user->create_and_get();
    $post1 = $this->create_post();
    $post2 = $this->create_post();
    $notification1 = $this->notification_repo->add(
      new Notification([
        'user_id' => $user1->ID,
        'post_id' => $post1->ID,
        'notification_type' => NotificationType::BRACKET_UPCOMING,
      ])
    );
    $notification2 = $this->notification_repo->add(
      new Notification([
        'user_id' => $user2->ID,
        'post_id' => $post1->ID,
        'notification_type' => NotificationType::BRACKET_UPCOMING,
      ])
    );
    $notification3 = $this->notification_repo->add(
      new Notification([
        'user_id' => $user1->ID,
        'post_id' => $post2->ID,
        'notification_type' => NotificationType::BRACKET_UPCOMING,
      ])
    );
    $found_notifications = $this->notification_repo->get([
      'notification_type' => NotificationType::BRACKET_UPCOMING,
      'post_id' => $post1->ID,
    ]);
    $this->assertCount(2, $found_notifications);
    $this->assertInstanceOf(Notification::class, $found_notifications[0]);
    $this->assertSame($notification1->id, $found_notifications[0]->id);
    $this->assertSame($user1->ID, $found_notifications[0]->user_id);
    $this->assertSame($post1->ID, $found_notifications[0]->post_id);
    $this->assertSame(
      NotificationType::BRACKET_UPCOMING,
      $found_notifications[0]->notification_type
    );
    $this->assertInstanceOf(Notification::class, $found_notifications[1]);
    $this->assertSame($notification2->id, $found_notifications[1]->id);
    $this->assertSame($user2->ID, $found_notifications[1]->user_id);
    $this->assertSame($post1->ID, $found_notifications[1]->post_id);
    $this->assertSame(
      NotificationType::BRACKET_UPCOMING,
      $found_notifications[1]->notification_type
    );
  }

  public function test_delete() {
    $user = self::factory()->user->create_and_get();
    $post = $this->create_post();
    $notification = $this->notification_repo->add(
      new Notification([
        'user_id' => $user->ID,
        'post_id' => $post->ID,
        'notification_type' => NotificationType::BRACKET_UPCOMING,
      ])
    );
    $this->notification_repo->delete($notification->id);
    $found_notification = $this->notification_repo->get([
      'id' => $notification->id,
      'single' => true,
    ]);
    $this->assertNull($found_notification);
  }

  public function test_create_duplicate_notification() {
    $user = self::factory()->user->create_and_get();
    $post = $this->create_post();
    $notification = $this->notification_repo->add(
      new Notification([
        'user_id' => $user->ID,
        'post_id' => $post->ID,
        'notification_type' => NotificationType::BRACKET_UPCOMING,
      ])
    );
    $duplicate = $this->notification_repo->add(
      new Notification([
        'user_id' => $user->ID,
        'post_id' => $post->ID,
        'notification_type' => NotificationType::BRACKET_UPCOMING,
      ])
    );
    $this->assertSame($notification->id, $duplicate->id);
  }
}
