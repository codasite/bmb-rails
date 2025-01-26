<?php

namespace WStrategies\BMB\Features\Notifications\Push;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\MulticastSendReport;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Includes\Utils;

class PushMessagingService {
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
   * Sends a notification to a user's devices and handles delivery reports
   *
   * @param NotificationType $type The type of notification
   * @param int $user_id Target user ID
   * @param string $title Notification title
   * @param string $message Notification body
   * @param string $image_url Optional image URL
   * @param array $data Optional additional data
   * @return MulticastSendReport The send report
   */
  public function send_notification(
    NotificationType $type,
    int $user_id,
    string $title = '',
    string $message = '',
    string $image_url = '',
    array $data = []
  ): MulticastSendReport {
    $tokens = $this->token_manager->get_target_tokens($type, $user_id);

    if (empty($tokens)) {
      return MulticastSendReport::withItems([]); // Return empty report if no tokens
    }

    $notification = Notification::create($title, $message, $image_url);
    $message = CloudMessage::new()
      ->withNotification($notification)
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
