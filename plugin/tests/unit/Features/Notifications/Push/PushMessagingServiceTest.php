<?php

namespace WStrategies\BMB\tests\unit\Features\Notifications\Push;

use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Includes\Utils;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\MessageTarget;
use PHPUnit\Framework\MockObject\MockObject;
use Kreait\Firebase\Messaging\MulticastSendReport;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Features\Notifications\Domain\Notification as BMBNotification;
use WStrategies\BMB\Features\Notifications\Push\FCMTokenManager;
use WStrategies\BMB\Features\Notifications\Push\Fakes\MessagingFake;
use WStrategies\BMB\Features\Notifications\Push\Fakes\SendReportFake;
use WStrategies\BMB\Features\Notifications\Push\PushMessagingService;

class PushMessagingServiceTest extends TestCase {
  private MessagingFake $messaging;
  /** @var MockObject&FCMTokenManager */
  private MockObject $device_manager;
  private PushMessagingService $service;

  public function setUp(): void {
    parent::setUp();
    \WP_Mock::setUp();

    $this->messaging = new MessagingFake();
    $this->device_manager = $this->createMock(FCMTokenManager::class);
    $this->service = new PushMessagingService(
      $this->messaging,
      $this->device_manager
    );
  }

  public function tearDown(): void {
    \WP_Mock::tearDown();
    parent::tearDown();
  }

  public function test_send_notification_handles_invalid_token(): void {
    $tokens = ['valid_token', 'invalid_token'];
    $this->device_manager->method('get_target_tokens')->willReturn($tokens);

    $this->device_manager
      ->expects($this->once())
      ->method('handle_failed_delivery')
      ->with('invalid_token');

    $this->messaging->configure_response(
      'invalid_token',
      fn($target) => SendReportFake::failure($target)->withInvalidTarget()
    );

    $notification = new BMBNotification([
      'notification_type' => NotificationType::TOURNAMENT_START,
      'user_id' => 1,
      'title' => 'Test Title',
      'message' => 'Test Message',
    ]);

    $report = $this->service->handle_notification($notification);

    $this->assertEquals(1, $report->successes()->count());
    $this->assertEquals(1, $report->failures()->count());
  }

  public function test_send_notification_handles_unknown_token(): void {
    $tokens = ['valid_token', 'unknown_token'];
    $this->device_manager->method('get_target_tokens')->willReturn($tokens);

    $this->device_manager
      ->expects($this->once())
      ->method('handle_failed_delivery')
      ->with('unknown_token');

    $this->messaging->configure_response(
      'unknown_token',
      fn($target) => SendReportFake::failure($target)->withUnknownToken()
    );

    $notification = new BMBNotification([
      'notification_type' => NotificationType::TOURNAMENT_START,
      'user_id' => 1,
      'title' => 'Test Title',
      'message' => 'Test Message',
    ]);

    $report = $this->service->handle_notification($notification);

    $this->assertEquals(1, $report->successes()->count());
    $this->assertEquals(1, $report->failures()->count());
  }

  public function test_send_notification_handles_invalid_message(): void {
    $tokens = ['valid_token', 'token_with_invalid_message'];
    $this->device_manager->method('get_target_tokens')->willReturn($tokens);

    // Should not try to remove token for invalid message
    $this->device_manager
      ->expects($this->never())
      ->method('handle_failed_delivery');

    $this->messaging->configure_response(
      'token_with_invalid_message',
      fn($target) => SendReportFake::failure($target)->withInvalidMessage()
    );

    $notification = new BMBNotification([
      'notification_type' => NotificationType::TOURNAMENT_START,
      'user_id' => 1,
      'title' => 'Test Title',
      'message' => 'Test Message',
    ]);

    $report = $this->service->handle_notification($notification);

    $this->assertEquals(1, $report->successes()->count());
    $this->assertEquals(1, $report->failures()->count());
  }

  public function test_send_notification_includes_all_parameters(): void {
    $this->device_manager->method('get_target_tokens')->willReturn(['token1']);

    $notification = new BMBNotification([
      'notification_type' => NotificationType::TOURNAMENT_START,
      'user_id' => 1,
      'title' => 'Test Title',
      'message' => 'Test Message',
      'link' => 'http://test.com/page',
    ]);

    $this->service->handle_notification($notification);

    $sent_messages = $this->messaging->getSentMessages();
    $message = $sent_messages[0]->jsonSerialize();

    // Verify message contains all parameters
    $this->assertArrayHasKey('notification', $message);
    $this->assertEquals('Test Title', $message['notification']['title']);
    $this->assertEquals('Test Message', $message['notification']['body']);
    $this->assertEquals(['link' => 'http://test.com/page'], $message['data']);
  }

  public function test_send_notification_adds_link_to_empty_data(): void {
    $this->device_manager->method('get_target_tokens')->willReturn(['token1']);

    $notification = new BMBNotification([
      'notification_type' => NotificationType::TOURNAMENT_START,
      'user_id' => 1,
      'title' => 'Test Title',
      'message' => 'Test Message',
      'link' => 'http://test.com/page',
    ]);

    $this->service->handle_notification($notification);

    $sent_messages = $this->messaging->getSentMessages();
    $message = $sent_messages[0]->jsonSerialize();

    $this->assertEquals(['link' => 'http://test.com/page'], $message['data']);
  }
}
