<?php

namespace WStrategies\BMB\Features\Notifications\Application;

use WStrategies\BMB\Features\Notifications\Domain\Notification;
use WStrategies\BMB\Features\Notifications\Domain\NotificationChannelInterface;
use WStrategies\BMB\Features\Notifications\Email\EmailService;
use WStrategies\BMB\Features\Notifications\Push\PushMessagingService;
use WStrategies\BMB\Features\Notifications\Email\MailchimpEmailServiceFactory;
use WStrategies\BMB\Features\Notifications\Push\PushMessagingServiceFactory;
use WStrategies\BMB\Includes\Utils;

class NotificationDispatcher {
  private readonly NotificationManager $notification_manager;
  private readonly PushMessagingService $push_service;
  private readonly EmailService $email_service;
  private readonly Utils $utils;

  public function __construct($args = []) {
    $this->notification_manager =
      $args['notification_manager'] ?? new NotificationManager();
    $this->push_service =
      $args['push_service'] ?? (new PushMessagingServiceFactory())->create();
    $this->email_service = $args['email_service'] ?? new EmailService();
    $this->utils = $args['utils'] ?? new Utils();
  }

  public function dispatch(Notification $notification): void {
    // Try to store the notification
    try {
      $stored = $this->notification_manager->handle_notification($notification);
      if ($stored) {
        $notification = $stored; // Use stored version if available
      }
    } catch (\Exception $e) {
      $this->utils->log_error(
        'Error storing notification: ' .
          $e->getMessage() .
          "\nStack trace:\n" .
          $e->getTraceAsString()
      );
      // Continue with original notification
    }

    // Try to send push notification
    try {
      $this->push_service->handle_notification($notification);
    } catch (\Exception $e) {
      $this->utils->log_error(
        'Error sending push notification: ' .
          $e->getMessage() .
          "\nStack trace:\n" .
          $e->getTraceAsString()
      );
    }

    // Try to send email notification
    try {
      $this->email_service->handle_notification($notification);
    } catch (\Exception $e) {
      $this->utils->log_error(
        'Error sending email notification: ' .
          $e->getMessage() .
          "\nStack trace:\n" .
          $e->getTraceAsString()
      );
    }
  }
}
