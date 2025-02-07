<?php

namespace WStrategies\BMB\Features\Notifications\Application;

use WStrategies\BMB\Features\Notifications\Domain\Notification;
use WStrategies\BMB\Features\Notifications\Email\EmailServiceInterface;
use WStrategies\BMB\Features\Notifications\Push\PushMessagingService;
use WStrategies\BMB\Email\Template\BracketEmailTemplate;
use WStrategies\BMB\Features\Notifications\Email\MailchimpEmailServiceFactory;
use WStrategies\BMB\Features\Notifications\Push\PushMessagingServiceFactory;
use WStrategies\BMB\Includes\Utils;

class NotificationDispatcher {
  private readonly PushMessagingService $push_service;
  private readonly EmailServiceInterface $email_service;
  private readonly NotificationManager $notification_manager;
  private readonly Utils $utils;

  public function __construct($args = []) {
    $this->push_service =
      $args['push_service'] ?? (new PushMessagingServiceFactory())->create();
    $this->email_service =
      $args['email_service'] ?? (new MailchimpEmailServiceFactory())->create();
    $this->notification_manager =
      $args['notification_manager'] ?? new NotificationManager();
    $this->utils = new Utils();
  }

  public function dispatch(Notification $notification): void {
    try {
      // Send push notification
      $this->push_service->send_notification([
        'type' => $notification->notification_type,
        'user_id' => $notification->user_id,
        'title' => $notification->title,
        'message' => $notification->message,
        'link' => $notification->link,
      ]);

      // Send email notification
      $user = get_user_by('id', $notification->user_id);
      if ($user) {
        $html = BracketEmailTemplate::render(
          $notification->message,
          $notification->link,
          'View Details'
        );

        $this->email_service->send(
          $user->user_email,
          $user->display_name,
          $notification->title,
          $notification->message,
          $html
        );
      }

      // Store notification
      $this->notification_manager->create_notification(
        $notification->user_id,
        $notification->title,
        $notification->message,
        $notification->notification_type,
        $notification->link
      );
    } catch (\Exception $e) {
      $this->utils->log_error(
        'Error dispatching notification: ' .
          $e->getMessage() .
          "\nStack trace:\n" .
          $e->getTraceAsString()
      );
    }
  }
}
