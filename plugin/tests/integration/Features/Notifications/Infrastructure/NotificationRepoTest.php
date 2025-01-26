<?php

namespace WStrategies\BMB\tests\integration\Features\Notifications\Infrastructure;

use WStrategies\BMB\Features\Notifications\Domain\Notification;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Features\Notifications\Infrastructure\NotificationRepo;
use WStrategies\BMB\Includes\Repository\Exceptions\RepositoryCreateException;
use WStrategies\BMB\Includes\Repository\Exceptions\RepositoryUpdateException;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class NotificationRepoTest extends WPBB_UnitTestCase {
  private NotificationRepo $repo;
  private $user;
  private $show_errors;

  public function set_up(): void {
    parent::set_up();
    $this->repo = new NotificationRepo();
    $this->user = $this->create_user();
    $this->show_errors = $this->repo->suppress_errors();
  }

  public function tear_down(): void {
    if ($this->show_errors) {
      $this->repo->show_errors();
    }
    parent::tear_down();
  }

  public function test_add_notification(): void {
    $notification = new Notification([
      'user_id' => $this->user->ID,
      'title' => 'Test Title',
      'message' => 'Test Message',
      'notification_type' => NotificationType::BRACKET_UPCOMING,
      'link' => 'https://example.com',
    ]);

    $result = $this->repo->add($notification);

    $this->assertNotNull($result);
    $this->assertEquals($this->user->ID, $result->user_id);
    $this->assertEquals('Test Title', $result->title);
    $this->assertEquals('Test Message', $result->message);
    $this->assertEquals(
      NotificationType::BRACKET_UPCOMING,
      $result->notification_type
    );
    $this->assertEquals('https://example.com', $result->link);
    $this->assertFalse($result->is_read);
  }

  public function test_get_notification(): void {
    $notification = new Notification([
      'user_id' => $this->user->ID,
      'title' => 'Test Title',
      'message' => 'Test Message',
      'notification_type' => NotificationType::BRACKET_UPCOMING,
    ]);
    $created = $this->repo->add($notification);

    $result = $this->repo->get(['id' => $created->id, 'single' => true]);

    $this->assertNotNull($result);
    $this->assertEquals($created->id, $result->id);
    $this->assertEquals($this->user->ID, $result->user_id);
    $this->assertEquals('Test Title', $result->title);
  }

  public function test_update_notification(): void {
    $notification = new Notification([
      'user_id' => $this->user->ID,
      'title' => 'Original Title',
      'message' => 'Original Message',
      'notification_type' => NotificationType::TOURNAMENT_START,
    ]);
    $created = $this->repo->add($notification);

    $updated = $this->repo->update($created->id, [
      'title' => 'Updated Title',
      'message' => 'Updated Message',
    ]);

    $this->assertNotNull($updated);
    $this->assertEquals($created->id, $updated->id);
    $this->assertEquals('Updated Title', $updated->title);
    $this->assertEquals('Updated Message', $updated->message);
  }

  public function test_delete_notification(): void {
    $notification = new Notification([
      'user_id' => $this->user->ID,
      'title' => 'Test Title',
      'message' => 'Test Message',
      'notification_type' => NotificationType::ROUND_COMPLETE,
    ]);
    $created = $this->repo->add($notification);

    $deleted = $this->repo->delete($created->id);
    $result = $this->repo->get(['id' => $created->id, 'single' => true]);

    $this->assertTrue($deleted);
    $this->assertNull($result);
  }

  public function test_get_by_user(): void {
    // Add multiple notifications for user
    $notification1 = new Notification([
      'user_id' => $this->user->ID,
      'title' => 'First Title',
      'message' => 'First Message',
      'notification_type' => NotificationType::BRACKET_RESULTS,
      'timestamp' => '2025-01-01 00:00:00',
    ]);
    $notification2 = new Notification([
      'user_id' => $this->user->ID,
      'title' => 'Second Title',
      'message' => 'Second Message',
      'notification_type' => NotificationType::TOURNAMENT_START,
      'timestamp' => '2025-01-02 00:00:00',
    ]);
    $this->repo->add($notification1);
    $this->repo->add($notification2);

    $notifications = $this->repo->get(['user_id' => $this->user->ID]);

    $this->assertCount(2, $notifications);
    $this->assertEquals('Second Title', $notifications[0]->title); // Most recent first
    $this->assertEquals('First Title', $notifications[1]->title);
  }

  public function test_mark_as_read(): void {
    $notification = new Notification([
      'user_id' => $this->user->ID,
      'title' => 'Test Title',
      'message' => 'Test Message',
      'notification_type' => NotificationType::BRACKET_UPCOMING,
    ]);
    $created = $this->repo->add($notification);

    $updated = $this->repo->update($created->id, ['is_read' => true]);

    $this->assertNotNull($updated);
    $this->assertTrue($updated->is_read);
  }

  public function test_delete_old_notifications(): void {
    global $wpdb;

    // Add an old notification
    $notification = new Notification([
      'user_id' => $this->user->ID,
      'title' => 'Old Title',
      'message' => 'Old Message',
      'notification_type' => NotificationType::ROUND_COMPLETE,
    ]);
    $created = $this->repo->add($notification);

    // Manually update timestamp to old date
    $table = NotificationRepo::table_name();
    $wpdb->update(
      $table,
      ['timestamp' => '2020-01-01 00:00:00'],
      ['id' => $created->id]
    );

    // Add a recent notification
    $notification2 = new Notification([
      'user_id' => $this->user->ID,
      'title' => 'Recent Title',
      'message' => 'Recent Message',
      'notification_type' => NotificationType::BRACKET_RESULTS,
    ]);
    $this->repo->add($notification2);

    $deleted_count = $this->repo->delete_old_notifications(30);
    $remaining = $this->repo->get(['user_id' => $this->user->ID]);

    $this->assertEquals(1, $deleted_count);
    $this->assertCount(1, $remaining);
    $this->assertEquals('Recent Title', $remaining[0]->title);
  }

  public function test_get_by_notification_type(): void {
    $upcoming_notification = new Notification([
      'user_id' => $this->user->ID,
      'title' => 'Upcoming Title',
      'message' => 'Upcoming Message',
      'notification_type' => NotificationType::BRACKET_UPCOMING,
    ]);
    $results_notification = new Notification([
      'user_id' => $this->user->ID,
      'title' => 'Results Title',
      'message' => 'Results Message',
      'notification_type' => NotificationType::BRACKET_RESULTS,
    ]);
    $this->repo->add($upcoming_notification);
    $this->repo->add($results_notification);

    $upcoming_notifications = $this->repo->get([
      'notification_type' => NotificationType::BRACKET_UPCOMING->value,
    ]);

    $this->assertCount(1, $upcoming_notifications);
    $this->assertEquals('Upcoming Title', $upcoming_notifications[0]->title);
  }

  public function test_get_unread_notifications(): void {
    $notification1 = new Notification([
      'user_id' => $this->user->ID,
      'title' => 'Unread',
      'message' => 'Unread Message',
      'notification_type' => NotificationType::TOURNAMENT_START,
    ]);
    $notification2 = new Notification([
      'user_id' => $this->user->ID,
      'title' => 'Read',
      'message' => 'Read Message',
      'notification_type' => NotificationType::BRACKET_UPCOMING,
      'is_read' => true,
    ]);
    $this->repo->add($notification1);
    $this->repo->add($notification2);

    $unread = $this->repo->get([
      'user_id' => $this->user->ID,
      'is_read' => false,
    ]);

    $this->assertCount(1, $unread);
    $this->assertEquals('Unread', $unread[0]->title);
  }

  public function test_cascade_delete_on_user_deletion(): void {
    $notification = new Notification([
      'user_id' => $this->user->ID,
      'title' => 'Test Title',
      'message' => 'Test Message',
      'notification_type' => NotificationType::BRACKET_RESULTS,
    ]);
    $this->repo->add($notification);

    wp_delete_user($this->user->ID);

    $remaining = $this->repo->get(['user_id' => $this->user->ID]);
    $this->assertEmpty($remaining);
  }

  public function test_invalid_update_fields(): void {
    $notification = new Notification([
      'user_id' => $this->user->ID,
      'title' => 'Test Title',
      'message' => 'Test Message',
      'notification_type' => NotificationType::ROUND_COMPLETE,
    ]);
    $created = $this->repo->add($notification);

    $this->expectException(RepositoryUpdateException::class);
    $this->repo->update($created->id, [
      'user_id' => 999, // Should not be able to update user_id
    ]);
  }

  public function test_sql_injection_prevention(): void {
    $malicious_title =
      "'; DROP TABLE " . NotificationRepo::table_name() . '; --';

    $notification = new Notification([
      'user_id' => $this->user->ID,
      'title' => $malicious_title,
      'message' => 'Test Message',
      'notification_type' => NotificationType::TOURNAMENT_START,
    ]);

    $created = $this->repo->add($notification);

    // Verify table still exists and notification was stored properly
    $result = $this->repo->get(['id' => $created->id, 'single' => true]);
    $this->assertNotNull($result);
    $this->assertEquals($malicious_title, $result->title);
  }
}
