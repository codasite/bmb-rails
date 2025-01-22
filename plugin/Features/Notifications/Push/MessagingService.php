<?php

namespace WStrategies\BMB\Features\Notifications\Push;

use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\MulticastSendReport;
use WStrategies\BMB\Features\Notifications\NotificationType;

class MessagingService {
  private Messaging $messaging;
  private FCMTokenManager $fcmDeviceManager;

  public function __construct(
    Messaging $messaging,
    FCMTokenManager $fcmDeviceManager
  ) {
    $this->messaging = $messaging;
    $this->fcmDeviceManager = $fcmDeviceManager;
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
  public function sendNotification(
    NotificationType $type,
    int $user_id,
    string $title = '',
    string $message = '',
    string $image_url = '',
    array $data = []
  ): MulticastSendReport {
    $tokens = $this->fcmDeviceManager->get_target_device_tokens(
      $type,
      $user_id
    );

    if (empty($tokens)) {
      return MulticastSendReport::withItems([]); // Return empty report if no tokens
    }

    $notification = Notification::create($title, $message, $image_url);
    $message = CloudMessage::new()
      ->withNotification($notification)
      ->withData($data);

    $sendReport = $this->messaging->sendMulticast($message, $tokens);

    // Handle the send report
    $this->handleSendReport($sendReport);

    return $sendReport;
  }

  /**
   * Processes the multicast send report and handles any failures
   *
   * @param MulticastSendReport $report The send report to process
   */
  private function handleSendReport(MulticastSendReport $report): void {
    // Log overall statistics
    error_log(
      sprintf(
        'FCM Notification Report - Success: %d, Failed: %d',
        $report->successes()->count(),
        $report->failures()->count()
      )
    );

    // Process failures using SendReport methods
    if ($report->hasFailures()) {
      foreach ($report->failures() as $failure) {
        $token = $failure->target()->value();

        // Handle different failure types
        if ($failure->messageTargetWasInvalid()) {
          error_log("Invalid token format: {$token}");
          $this->fcmDeviceManager->handle_failed_delivery($token);
        } elseif ($failure->messageWasSentToUnknownToken()) {
          error_log("Unknown token (not in Firebase): {$token}");
          $this->fcmDeviceManager->handle_failed_delivery($token);
        } elseif ($failure->messageWasInvalid()) {
          error_log("Invalid message format for token: {$token}");
        } else {
          // Log other errors with full error message
          $error = $failure->error();
          error_log(
            sprintf(
              'FCM delivery failed for token %s: %s',
              $token,
              $error ? $error->getMessage() : 'Unknown error'
            )
          );
        }
      }
    }

    // Store valid tokens for future reference if needed
    $validTokens = $report->validTokens();
  }
}
