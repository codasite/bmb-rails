<?php

namespace WStrategies\BMB\tests\integration\Features\Notifications\Application;

use WStrategies\BMB\Features\Notifications\Application\NotificationManager;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Features\Notifications\Infrastructure\NotificationRepo;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;
use WStrategies\BMB\Features\Notifications\Domain\Notification;
use WStrategies\BMB\Includes\Repository\Exceptions\RepositoryCreateException;

class NotificationManagerTest extends WPBB_UnitTestCase {
  private NotificationManager $notification_manager;
  private NotificationRepo $notification_repo;
  private $user;

  public function set_up(): void {
    parent::set_up();
    $this->notification_repo = new NotificationRepo();
    $this->notification_manager = new NotificationManager([
      'notification_repo' => $this->notification_repo,
    ]);
    $this->user = $this->create_user();
  }

  public function test_create_notification(): void {
    $notification = $this->notification_manager->handle_notification(
      new Notification([
        'user_id' => $this->user->ID,
        'title' => 'Test Title',
        'message' => 'Test Message',
        'notification_type' => NotificationType::SYSTEM,
        'link' => 'https://example.com',
      ])
    );

    $this->assertNotNull($notification);
    $this->assertEquals($this->user->ID, $notification->user_id);
    $this->assertEquals('Test Title', $notification->title);
    $this->assertEquals('Test Message', $notification->message);
    $this->assertEquals(
      NotificationType::SYSTEM,
      $notification->notification_type
    );
    $this->assertEquals('https://example.com', $notification->link);
    $this->assertFalse($notification->is_read);
  }

  public function test_create_notification_with_invalid_user_id(): void {
    global $wpdb;
    $show_errors = $wpdb->suppress_errors();

    try {
      $this->expectException(RepositoryCreateException::class);

      $this->notification_manager->handle_notification(
        new Notification([
          'user_id' => 99999, // Non-existent user ID
          'title' => 'Test Title',
          'message' => 'Test Message',
          'notification_type' => NotificationType::SYSTEM,
          'link' => 'https://example.com',
        ])
      );
    } finally {
      if ($show_errors) {
        $wpdb->show_errors();
      }
    }
  }

  public function test_mark_as_read(): void {
    // Create an unread notification
    $notification = $this->notification_manager->handle_notification(
      new Notification([
        'user_id' => $this->user->ID,
        'title' => 'Test Title',
        'message' => 'Test Message',
        'notification_type' => NotificationType::SYSTEM,
        'link' => 'https://example.com',
      ])
    );

    $this->assertFalse($notification->is_read);

    // Mark it as read
    $updated = $this->notification_manager->mark_as_read($notification->id);

    $this->assertNotNull($updated);
    $this->assertTrue($updated->is_read);

    // Verify in database
    $found = $this->notification_repo->get([
      'id' => $notification->id,
      'single' => true,
    ]);
    $this->assertTrue($found->is_read);
  }

  public function test_mark_as_read_nonexistent_notification(): void {
    $result = $this->notification_manager->mark_as_read('99999');
    $this->assertNull($result);
  }

  public function test_mark_all_as_read(): void {
    // Create multiple unread notifications for the same user
    $this->notification_manager->handle_notification(
      new Notification([
        'user_id' => $this->user->ID,
        'title' => 'First Title',
        'message' => 'First Message',
        'notification_type' => NotificationType::SYSTEM,
        'link' => 'https://example.com',
      ])
    );

    $this->notification_manager->handle_notification(
      new Notification([
        'user_id' => $this->user->ID,
        'title' => 'Second Title',
        'message' => 'Second Message',
        'notification_type' => NotificationType::BRACKET_UPCOMING,
        'link' => 'https://example.com',
      ])
    );

    // Create a notification for a different user that shouldn't be affected
    $other_user = $this->create_user();
    $this->notification_manager->handle_notification(
      new Notification([
        'user_id' => $other_user->ID,
        'title' => 'Other Title',
        'message' => 'Other Message',
        'notification_type' => NotificationType::SYSTEM,
        'link' => 'https://example.com',
      ])
    );

    // Mark all as read for the first user
    $updated_count = $this->notification_manager->mark_all_as_read(
      $this->user->ID
    );

    // Should have updated 2 notifications
    $this->assertEquals(2, $updated_count);

    // Verify all notifications for the first user are read
    $notifications = $this->notification_repo->get([
      'user_id' => $this->user->ID,
    ]);
    foreach ($notifications as $notification) {
      $this->assertTrue($notification->is_read);
    }

    // Verify the other user's notification is still unread
    $other_notifications = $this->notification_repo->get([
      'user_id' => $other_user->ID,
    ]);
    foreach ($other_notifications as $notification) {
      $this->assertFalse($notification->is_read);
    }
  }

  public function test_mark_all_as_read_no_unread_notifications(): void {
    // Create a read notification
    $notification = $this->notification_manager->handle_notification(
      new Notification([
        'user_id' => $this->user->ID,
        'title' => 'Test Title',
        'message' => 'Test Message',
        'notification_type' => NotificationType::SYSTEM,
        'link' => 'https://example.com',
      ])
    );
    $this->notification_repo->update($notification->id, ['is_read' => true]);

    // Try to mark all as read
    $updated_count = $this->notification_manager->mark_all_as_read(
      $this->user->ID
    );

    // Should have updated 0 notifications
    $this->assertEquals(0, $updated_count);
  }

  public function test_mark_all_as_read_nonexistent_user(): void {
    $updated_count = $this->notification_manager->mark_all_as_read(99999);
    $this->assertEquals(0, $updated_count);
  }
}
