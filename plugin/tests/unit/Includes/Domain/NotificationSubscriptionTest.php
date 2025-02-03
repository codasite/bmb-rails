<?php
namespace WStrategies\BMB\tests\unit\Includes\Domain;

use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Features\Notifications\Domain\NotificationSubscription;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;

class NotificationSubscriptionTest extends TestCase {
  public function testConstructor() {
    $data = [
      'id' => '1',
      'user_id' => '2',
      'post_id' => '3',
      'notification_type' => 'bracket_upcoming',
    ];

    $notification = new NotificationSubscription($data);

    $this->assertInstanceOf(NotificationSubscription::class, $notification);
    $this->assertSame(1, $notification->id);
    $this->assertSame(2, $notification->user_id);
    $this->assertSame(3, $notification->post_id);
    $this->assertSame(
      NotificationType::BRACKET_UPCOMING,
      $notification->notification_type
    );
  }

  public function testConstructorNoId() {
    $data = [
      'user_id' => '2',
      'post_id' => '3',
      'notification_type' => 'bracket_upcoming',
    ];

    $notification = new NotificationSubscription($data);

    $this->assertInstanceOf(NotificationSubscription::class, $notification);
    $this->assertNull($notification->id);
    $this->assertSame(2, $notification->user_id);
    $this->assertSame(3, $notification->post_id);
    $this->assertSame(
      NotificationType::BRACKET_UPCOMING,
      $notification->notification_type
    );
  }
}
