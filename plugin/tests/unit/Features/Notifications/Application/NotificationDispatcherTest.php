<?php

namespace WStrategies\BMB\tests\unit\Features\Notifications\Application;

use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Features\Notifications\Application\NotificationDispatcher;
use WStrategies\BMB\Features\Notifications\Application\NotificationManager;
use WStrategies\BMB\Features\Notifications\Domain\Notification;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Features\Notifications\Email\EmailService;
use WStrategies\BMB\Features\Notifications\Push\PushMessagingService;
use WStrategies\BMB\Includes\Utils;

class NotificationDispatcherTest extends TestCase {
  private Notification $test_notification;

  public function setUp(): void {
    parent::setUp();
    $this->test_notification = new Notification([
      'user_id' => 1,
      'title' => 'Test Title',
      'message' => 'Test Message',
      'notification_type' => NotificationType::BRACKET_UPCOMING,
      'link' => 'https://example.com',
    ]);
  }

  public function test_should_dispatch_to_all_channels_successfully() {
    // Create mocks
    $notification_manager = $this->createMock(NotificationManager::class);
    $push_service = $this->createMock(PushMessagingService::class);
    $email_service = $this->createMock(EmailService::class);

    // Set expectations
    $stored_notification = clone $this->test_notification;
    $stored_notification->id = '123';

    $notification_manager
      ->expects($this->once())
      ->method('handle_notification')
      ->with($this->test_notification)
      ->willReturn($stored_notification);

    $push_service
      ->expects($this->once())
      ->method('handle_notification')
      ->with($stored_notification);

    $email_service
      ->expects($this->once())
      ->method('handle_notification')
      ->with($stored_notification);

    // Create dispatcher with mocks
    $dispatcher = new NotificationDispatcher([
      'notification_manager' => $notification_manager,
      'push_service' => $push_service,
      'email_service' => $email_service,
    ]);

    // Dispatch notification
    $dispatcher->dispatch($this->test_notification);
  }

  public function test_should_continue_with_original_notification_if_storage_fails() {
    // Create mocks
    $notification_manager = $this->createMock(NotificationManager::class);
    $push_service = $this->createMock(PushMessagingService::class);
    $email_service = $this->createMock(EmailService::class);
    $utils = $this->createMock(Utils::class);

    // Set expectations
    $notification_manager
      ->expects($this->once())
      ->method('handle_notification')
      ->willThrowException(new \Exception('Storage failed'));

    $utils->expects($this->once())->method('log_error');

    $push_service
      ->expects($this->once())
      ->method('handle_notification')
      ->with($this->test_notification);

    $email_service
      ->expects($this->once())
      ->method('handle_notification')
      ->with($this->test_notification);

    // Create dispatcher with mocks
    $dispatcher = new NotificationDispatcher([
      'notification_manager' => $notification_manager,
      'push_service' => $push_service,
      'email_service' => $email_service,
      'utils' => $utils,
    ]);

    // Dispatch notification
    $dispatcher->dispatch($this->test_notification);
  }

  public function test_should_continue_if_push_notification_fails() {
    // Create mocks
    $notification_manager = $this->createMock(NotificationManager::class);
    $push_service = $this->createMock(PushMessagingService::class);
    $email_service = $this->createMock(EmailService::class);
    $utils = $this->createMock(Utils::class);

    // Set expectations
    $stored_notification = clone $this->test_notification;
    $stored_notification->id = '123';

    $notification_manager
      ->expects($this->once())
      ->method('handle_notification')
      ->willReturn($stored_notification);

    $push_service
      ->expects($this->once())
      ->method('handle_notification')
      ->willThrowException(new \Exception('Push failed'));

    $utils->expects($this->once())->method('log_error');

    $email_service
      ->expects($this->once())
      ->method('handle_notification')
      ->with($stored_notification);

    // Create dispatcher with mocks
    $dispatcher = new NotificationDispatcher([
      'notification_manager' => $notification_manager,
      'push_service' => $push_service,
      'email_service' => $email_service,
      'utils' => $utils,
    ]);

    // Dispatch notification
    $dispatcher->dispatch($this->test_notification);
  }

  public function test_should_continue_if_email_notification_fails() {
    // Create mocks
    $notification_manager = $this->createMock(NotificationManager::class);
    $push_service = $this->createMock(PushMessagingService::class);
    $email_service = $this->createMock(EmailService::class);
    $utils = $this->createMock(Utils::class);

    // Set expectations
    $stored_notification = clone $this->test_notification;
    $stored_notification->id = '123';

    $notification_manager
      ->expects($this->once())
      ->method('handle_notification')
      ->willReturn($stored_notification);

    $push_service
      ->expects($this->once())
      ->method('handle_notification')
      ->with($stored_notification);

    $email_service
      ->expects($this->once())
      ->method('handle_notification')
      ->willThrowException(new \Exception('Email failed'));

    $utils->expects($this->once())->method('log_error');

    // Create dispatcher with mocks
    $dispatcher = new NotificationDispatcher([
      'notification_manager' => $notification_manager,
      'push_service' => $push_service,
      'email_service' => $email_service,
      'utils' => $utils,
    ]);

    // Dispatch notification
    $dispatcher->dispatch($this->test_notification);
  }

  public function test_should_use_original_notification_if_storage_returns_null() {
    // Create mocks
    $notification_manager = $this->createMock(NotificationManager::class);
    $push_service = $this->createMock(PushMessagingService::class);
    $email_service = $this->createMock(EmailService::class);

    // Set expectations
    $notification_manager
      ->expects($this->once())
      ->method('handle_notification')
      ->willReturn(null);

    $push_service
      ->expects($this->once())
      ->method('handle_notification')
      ->with($this->test_notification);

    $email_service
      ->expects($this->once())
      ->method('handle_notification')
      ->with($this->test_notification);

    // Create dispatcher with mocks
    $dispatcher = new NotificationDispatcher([
      'notification_manager' => $notification_manager,
      'push_service' => $push_service,
      'email_service' => $email_service,
    ]);

    // Dispatch notification
    $dispatcher->dispatch($this->test_notification);
  }
}
