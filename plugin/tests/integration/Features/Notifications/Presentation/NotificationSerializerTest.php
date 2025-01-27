<?php

namespace WStrategies\BMB\tests\integration\Features\Notifications\Presentation;

use DateTime;
use WStrategies\BMB\Features\Notifications\Domain\Notification;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Features\Notifications\Presentation\NotificationSerializer;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class NotificationSerializerTest extends WPBB_UnitTestCase {
  private NotificationSerializer $serializer;

  protected function setUp(): void {
    parent::setUp();
    $this->serializer = new NotificationSerializer();
  }

  public function test_serialize_notification(): void {
    // Arrange
    $notification = new Notification([
      'id' => '123',
      'user_id' => 456,
      'title' => 'Test Title',
      'message' => 'Test Message',
      'timestamp' => new DateTime('2024-03-20 10:00:00'),
      'is_read' => true,
      'link' => 'https://example.com',
      'notification_type' => NotificationType::SYSTEM,
    ]);

    // Act
    $result = $this->serializer->serialize($notification);

    // Assert
    $this->assertEquals(
      [
        'id' => '123',
        'user_id' => 456,
        'title' => 'Test Title',
        'message' => 'Test Message',
        'timestamp' => '2024-03-20T10:00:00+00:00',
        'is_read' => true,
        'link' => 'https://example.com',
        'notification_type' => 'system',
      ],
      $result
    );
  }

  public function test_deserialize_notification(): void {
    // Arrange
    $data = [
      'id' => '123',
      'user_id' => 456,
      'title' => 'Test Title',
      'message' => 'Test Message',
      'timestamp' => '2024-03-20T10:00:00+00:00',
      'is_read' => true,
      'link' => 'https://example.com',
      'notification_type' => 'system',
    ];

    // Act
    $notification = $this->serializer->deserialize($data);

    // Assert
    $this->assertInstanceOf(Notification::class, $notification);
    $this->assertEquals('123', $notification->id);
    $this->assertEquals(456, $notification->user_id);
    $this->assertEquals('Test Title', $notification->title);
    $this->assertEquals('Test Message', $notification->message);
    $this->assertEquals(
      '2024-03-20 10:00:00',
      $notification->timestamp->format('Y-m-d H:i:s')
    );
    $this->assertTrue($notification->is_read);
    $this->assertEquals('https://example.com', $notification->link);
    $this->assertEquals(
      NotificationType::SYSTEM,
      $notification->notification_type
    );
  }

  public function test_deserialize_minimal_notification(): void {
    // Arrange
    $data = [
      'user_id' => 456,
      'title' => 'Test Title',
      'message' => 'Test Message',
      'timestamp' => '2024-03-20T10:00:00+00:00',
      'notification_type' => 'system',
    ];

    // Act
    $notification = $this->serializer->deserialize($data);

    // Assert
    $this->assertInstanceOf(Notification::class, $notification);
    $this->assertNull($notification->id);
    $this->assertEquals(456, $notification->user_id);
    $this->assertEquals('Test Title', $notification->title);
    $this->assertEquals('Test Message', $notification->message);
    $this->assertFalse($notification->is_read);
    $this->assertNull($notification->link);
    $this->assertEquals(
      NotificationType::SYSTEM,
      $notification->notification_type
    );
  }

  public function test_serialize_null_returns_empty_array(): void {
    $this->assertEquals([], $this->serializer->serialize(null));
  }
}
