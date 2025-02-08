<?php

namespace WStrategies\BMB\Features\Notifications\Push;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FCMNotification;
use Kreait\Firebase\Messaging\MulticastSendReport;
use WStrategies\BMB\Features\Notifications\Domain\Notification;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Features\Notifications\Domain\NotificationChannelInterface;
use WStrategies\BMB\Includes\Utils;

class PushMessagingService implements NotificationChannelInterface {
  private Messaging $messaging;
  private FCMTokenManager $token_manager;
  private Utils $utils;

  public function __construct(
    Messaging $messaging,
    FCMTokenManager $token_manager
  ) {
    $this->messaging = $messaging;
    $this->token_manager = $token_manager;
    $this->utils = new Utils();
  }

  /**
   * Handles sending a push notification
   *
   * @param Notification $notification The notification to send
   * @return MulticastSendReport The send report
   */
  public function handle_notification(
    Notification $notification
  ): MulticastSendReport {
    $tokens = $this->token_manager->get_target_tokens(
      $notification->notification_type,
      $notification->user_id
    );

    if (empty($tokens)) {
      return MulticastSendReport::withItems([]); // Return empty report if no tokens
    }

    $data = [];
    if (!empty($notification->link)) {
      $data['link'] = $notification->link;
    }
    if (!empty($notification->id)) {
      $data['id'] = $notification->id;
    }

    $fcm_notification = FCMNotification::create(
      $notification->title,
      $notification->message
    );

    $message = CloudMessage::new()
      ->withNotification($fcm_notification)
      ->withData($data);

    $sendReport = $this->messaging->sendMulticast($message, $tokens);

    // Handle the send report
    $this->handle_send_report($sendReport);

    return $sendReport;
  }

  /**
   * Processes the multicast send report and handles any failures
   *
   * @param MulticastSendReport $report The send report to process
   */
  private function handle_send_report(MulticastSendReport $report): void {
    // Log overall statistics
    $this->utils->log(
      sprintf(
        'FCM Notification Report - Success: %d, Failed: %d',
        $report->successes()->count(),
        $report->failures()->count()
      ),
      'info'
    );

    // Process failures using SendReport methods
    if ($report->hasFailures()) {
      foreach ($report->failures()->getItems() as $failure) {
        $token = $failure->target()->value();

        // Handle different failure types
        if ($failure->messageTargetWasInvalid()) {
          $this->utils->log_error("Invalid token format: {$token}");
          $this->token_manager->handle_failed_delivery($token);
        } elseif ($failure->messageWasSentToUnknownToken()) {
          $this->utils->log_error("Unknown token (not in Firebase): {$token}");
          $this->token_manager->handle_failed_delivery($token);
        } elseif ($failure->messageWasInvalid()) {
          $this->utils->log_error("Invalid message format for token: {$token}");
        } else {
          // Log other errors with full error message
          $error = $failure->error();
          $this->utils->log_error(
            sprintf(
              'FCM delivery failed for token %s: %s',
              $token,
              $error ? $error->getMessage() : 'Unknown error'
            )
          );
        }
      }
    }
  }
}
