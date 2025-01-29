<?php

namespace WStrategies\BMB\tests\integration\Features\Notifications\Presentation;

use DateTime;
use WStrategies\BMB\Features\Notifications\Domain\Notification;
use WStrategies\BMB\tests\integration\RestApiTestCase;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Features\Notifications\Infrastructure\NotificationRepo;

class NotificationApiTest extends RestApiTestCase {
  const API_ENDPOINT = '/bmb/v1/notifications';
  private $user;
  private NotificationRepo $repository;

  public function set_up(): void {
    parent::set_up();
    $this->user = $this->create_user();
    $this->repository = new NotificationRepo();
    wp_set_current_user($this->user->ID);
  }

  public function test_get_notifications_returns_empty_array_when_no_notifications(): void {
    $response = $this->get(self::API_ENDPOINT);

    $this->assertResponseIsSuccessful($response);
    $this->assertIsArray($response->get_data());
    $this->assertEmpty($response->get_data());
  }

  public function test_get_notifications_returns_user_notifications(): void {
    // Create notifications for current user
    $notification1 = $this->create_notification([
      'user_id' => $this->user->ID,
      'title' => 'Test 1',
      'message' => 'Message 1',
      'notification_type' => NotificationType::BRACKET_UPCOMING,
      'timestamp' => '2025-01-01 00:00:00',
    ]);

    $notification2 = $this->create_notification([
      'user_id' => $this->user->ID,
      'title' => 'Test 2',
      'message' => 'Message 2',
      'notification_type' => NotificationType::BRACKET_RESULTS,
      'timestamp' => '2025-01-01 00:10:00',
    ]);

    // Create notification for different user
    $other_user = $this->create_user();
    $this->create_notification([
      'user_id' => $other_user->ID,
      'title' => 'Other User',
      'message' => 'Other Message',
      'notification_type' => NotificationType::BRACKET_UPCOMING,
    ]);

    $response = $this->get(self::API_ENDPOINT);
    $data = $response->get_data();

    $this->assertResponseIsSuccessful($response);
    $this->assertCount(2, $data);

    // Notifications should be ordered by timestamp DESC
    $this->assertEquals($notification2->id, $data[0]['id']);
    $this->assertEquals($notification1->id, $data[1]['id']);
  }

  public function test_delete_notification(): void {
    $notification = $this->create_notification([
      'user_id' => $this->user->ID,
      'title' => 'Test',
      'message' => 'Message',
      'notification_type' => NotificationType::SYSTEM,
    ]);

    $response = $this->delete(self::API_ENDPOINT . '/' . $notification->id);

    $this->assertResponseIsSuccessful($response);
    $this->assertTrue($response->get_data()['deleted']);

    // Verify notification is deleted
    $get_response = $this->get(self::API_ENDPOINT);
    $this->assertEmpty($get_response->get_data());
  }

  public function test_cannot_delete_other_users_notification(): void {
    $other_user = $this->create_user();
    $notification = $this->create_notification([
      'user_id' => $other_user->ID,
      'title' => 'Other User',
      'message' => 'Other Message',
      'notification_type' => NotificationType::SYSTEM,
    ]);

    $response = $this->delete(self::API_ENDPOINT . '/' . $notification->id);

    $this->assertResponseStatus(404, $response);

    // Verify notification still exists
    wp_set_current_user($other_user->ID);
    $get_response = $this->get(self::API_ENDPOINT);
    $this->assertCount(1, $get_response->get_data());
  }

  public function test_unauthorized_access(): void {
    wp_set_current_user(0);

    $response = $this->get(self::API_ENDPOINT);
    $this->assertResponseStatus(401, $response);
  }

  public function test_unsupported_methods_on_root_endpoint(): void {
    $responses = [
      $this->post(self::API_ENDPOINT),
      $this->put(self::API_ENDPOINT),
      $this->delete(self::API_ENDPOINT),
    ];

    foreach ($responses as $response) {
      $this->assertResponseStatus(
        404,
        $response,
        'Method should not be allowed on root endpoint'
      );
    }
  }

  public function test_unsupported_methods_on_item_endpoint(): void {
    $notification = $this->create_notification([
      'user_id' => $this->user->ID,
      'title' => 'Test',
      'message' => 'Message',
      'notification_type' => NotificationType::SYSTEM,
    ]);

    $item_endpoint = self::API_ENDPOINT . '/' . $notification->id;
    $responses = [
      $this->get($item_endpoint),
      $this->post($item_endpoint),
      $this->put($item_endpoint),
    ];

    foreach ($responses as $response) {
      $this->assertResponseStatus(
        404,
        $response,
        'Method should not be allowed on item endpoint'
      );
    }
  }

  /**
   * Helper method to create a notification.
   */
  private function create_notification(array $args): ?object {
    $data = array_merge(
      [
        'user_id' => $this->user->ID,
        'title' => 'Test Notification',
        'message' => 'Test Message',
        'notification_type' => NotificationType::SYSTEM,
        'is_read' => false,
        'link' => null,
      ],
      $args
    );
    $notification = new Notification($data);

    return $this->repository->add($notification);
  }
}
