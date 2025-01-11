<?php

namespace WStrategies\BMB\Features\Notifications\Push;

use WStrategies\BMB\Features\Notifications\NotificationType;

/**
 * Manages device-level notification operations.
 *
 * Handles:
 * - Device preference management
 * - Notification targeting
 * - Token validation
 * - Device status management
 * - Failed delivery cleanup
 */
class FCMDeviceManager {
  private FCMTokenRepo $token_repo;

  public function __construct(FCMTokenRepo $token_repo) {
    $this->token_repo = $token_repo;
  }

  /**
   * Gets all active devices that should receive a notification.
   *
   * @param NotificationType $type Notification type
   * @param int[] $user_ids Target user IDs
   * @return array Valid FCM tokens for notification
   */
  public function getTargetDevices(
    NotificationType $type,
    array $user_ids
  ): array {
    $devices = [];
    foreach ($user_ids as $user_id) {
      $user_devices = $this->token_repo->get_user_devices($user_id);
      foreach ($user_devices as $device) {
        if ($this->shouldReceiveNotification($device, $type)) {
          $devices[] = $device;
        }
      }
    }
    return $devices;
  }

  /**
   * Checks if device should receive notification based on preferences.
   *
   * @param array $device Device data from repository
   * @param NotificationType $type Type of notification
   * @return bool Whether device should receive notification
   */
  private function shouldReceiveNotification(
    array $device,
    NotificationType $type
  ): bool {
    // Skip inactive devices (no activity in last 30 days)
    $last_used = strtotime($device['last_used_at']);
    if ($last_used < strtotime('-30 days')) {
      return false;
    }

    // TODO: Implement preference checking based on notification type
    // This will be expanded when we add notification preferences
    switch ($type) {
      case NotificationType::TOURNAMENT_START:
      case NotificationType::ROUND_COMPLETE:
      case NotificationType::BRACKET_RESULTS:
        return true; // Default to enabled for core notifications

      default:
        return false; // Disable unknown notification types
    }
  }

  /**
   * Handles failed notification delivery.
   *
   * Called when FCM reports a token as invalid or when delivery fails.
   * Removes the invalid token to prevent future delivery attempts.
   *
   * @param string $token The FCM token that failed
   */
  public function handleFailedDelivery(string $token): void {
    // Get device info before deletion for logging
    $device = $this->token_repo->get(['token' => $token, 'single' => true]);

    if ($device) {
      // Delete the invalid token by device info
      $this->token_repo->delete_by_device(
        (int) $device['user_id'],
        $device['device_id']
      );

      // TODO: Log token removal for monitoring
      error_log(
        sprintf(
          'Removed invalid FCM token for user %d device %s',
          $device['user_id'],
          $device['device_id']
        )
      );
    }
  }

  /**
   * Cleans up inactive tokens.
   *
   * @param int $days_threshold Number of days of inactivity before cleanup
   * @return int Number of tokens removed
   */
  public function cleanupInactiveTokens(int $days_threshold = 30): int {
    return $this->token_repo->delete_inactive_tokens($days_threshold);
  }

  /**
   * Updates device app version.
   *
   * @param int $user_id User ID
   * @param string $device_id Device identifier
   * @param string $app_version New app version
   * @return bool Success status
   */
  public function updateAppVersion(
    int $user_id,
    string $device_id,
    string $app_version
  ): bool {
    return $this->token_repo->update_app_version(
      $user_id,
      $device_id,
      $app_version
    );
  }
}
