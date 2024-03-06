<?php
namespace WStrategies\BMB\tests\integration\Includes\domain;

use WStrategies\BMB\Includes\Domain\Notification;
use WStrategies\BMB\Includes\Domain\NotificationType;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class NotificationTest extends WPBB_UnitTestCase {
  public function testConstructor() {
    $data = [
      'id' => '1',
      'user_id' => '2',
      'post_id' => '3',
      'notification_type' => 'bracket_upcoming',
    ];

    $notification = new Notification($data);

    $this->assertInstanceOf(Notification::class, $notification);
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

    $notification = new Notification($data);

    $this->assertInstanceOf(Notification::class, $notification);
    $this->assertNull($notification->id);
    $this->assertSame(2, $notification->user_id);
    $this->assertSame(3, $notification->post_id);
    $this->assertSame(
      NotificationType::BRACKET_UPCOMING,
      $notification->notification_type
    );
  }
}
