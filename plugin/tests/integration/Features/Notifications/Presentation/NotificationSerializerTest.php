<?php

namespace WStrategies\BMB\tests\integration\Features\Notifications\Presentation;

use DateTime;
use WStrategies\BMB\Features\Notifications\Domain\Notification;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Features\Notifications\Presentation\NotificationSerializer;
use WStrategies\BMB\Includes\Domain\ValidationException;
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
      'notification_type' => 'system',
    ];

    // Act
    $notification = $this->serializer->deserialize($data);

    // Assert
    $this->assertInstanceOf(Notification::class, $notification);
    $this->assertNull($notification->id);
    $this->assertEquals(456, $notification->user_id);
    $this->assertEquals('Test Title', $notification->title);
    $this->assertNull($notification->link);
    $this->assertEquals(
      NotificationType::SYSTEM,
      $notification->notification_type
    );
  }

  public function test_serialize_null_returns_empty_array(): void {
    $this->assertEquals([], $this->serializer->serialize(null));
  }

  /**
   * @dataProvider provideInvalidNotificationData
   */
  public function test_deserialize_throws_validation_exception_for_missing_required_fields(
    array $data,
    string $expectedMessage
  ): void {
    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage($expectedMessage);

    $this->serializer->deserialize($data);
  }

  public function provideInvalidNotificationData(): array {
    return [
      'missing all required fields' => [
        [],
        'Missing required fields: user_id, title, notification_type',
      ],
      'missing user_id' => [
        [
          'title' => 'Test Title',
          'message' => 'Test Message',
          'timestamp' => '2024-03-20T10:00:00+00:00',
          'notification_type' => 'system',
        ],
        'Missing required fields: user_id',
      ],
      'missing title' => [
        [
          'user_id' => 456,
          'message' => 'Test Message',
          'timestamp' => '2024-03-20T10:00:00+00:00',
          'notification_type' => 'system',
        ],
        'Missing required fields: title',
      ],
      'missing notification_type' => [
        [
          'user_id' => 456,
          'title' => 'Test Title',
          'message' => 'Test Message',
          'timestamp' => '2024-03-20T10:00:00+00:00',
        ],
        'Missing required fields: notification_type',
      ],
    ];
  }

  public function test_deserialize_throws_validation_exception_for_invalid_notification_type(): void {
    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage(
      'Invalid enum value "invalid_type" for WStrategies\BMB\Features\Notifications\Domain\NotificationType'
    );

    $this->serializer->deserialize([
      'user_id' => 456,
      'title' => 'Test Title',
      'message' => 'Test Message',
      'timestamp' => '2024-03-20T10:00:00+00:00',
      'notification_type' => 'invalid_type',
    ]);
  }

  public function test_deserialize_throws_validation_exception_for_invalid_timestamp(): void {
    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('Invalid date format');

    $this->serializer->deserialize([
      'user_id' => 456,
      'title' => 'Test Title',
      'message' => 'Test Message',
      'timestamp' => 'invalid-date',
      'notification_type' => 'system',
    ]);
  }
}
