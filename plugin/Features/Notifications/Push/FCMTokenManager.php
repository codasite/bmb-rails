<?php

namespace WStrategies\BMB\Features\Notifications\Push;

use WStrategies\BMB\Includes\Utils;
use WStrategies\BMB\Features\Notifications\NotificationType;
use WStrategies\BMB\Includes\Hooks\HooksInterface;
use WStrategies\BMB\Includes\Hooks\Loader;
use WStrategies\BMB\Features\Notifications\Push\Exceptions\TokenRegistrationException;
use WStrategies\BMB\Features\Notifications\Push\Exceptions\TokenUpdateException;

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
class FCMTokenManager implements HooksInterface {
  private FCMTokenRepo $token_repo;
  private const CLEANUP_HOOK = 'wpbb_fcm_cleanup_hook';
  private const CLEANUP_SCHEDULE = 'daily';
  private const DEFAULT_INACTIVE_DAYS = 30;

  public function __construct(array $args = []) {
    $this->token_repo = $args['token_repo'] ?? new FCMTokenRepo();
  }

  public function load(Loader $loader): void {
    $loader->add_action('init', [$this, 'schedule_cleanup_cron']);
    $loader->add_action(self::CLEANUP_HOOK, [$this, 'run_cleanup']);
  }

  /**
   * Schedule the cleanup cron job if not already scheduled
   */
  public function schedule_cleanup_cron(): void {
    if (!wp_next_scheduled(self::CLEANUP_HOOK)) {
      wp_schedule_event(time(), self::CLEANUP_SCHEDULE, self::CLEANUP_HOOK);
    }
  }

  /**
   * Cron job handler to clean up inactive tokens
   */
  public function run_cleanup(): void {
    $removed = $this->cleanup_inactive_tokens(self::DEFAULT_INACTIVE_DAYS);

    if ($removed > 0) {
      (new Utils())->log(
        sprintf('Cleaned up %d inactive FCM tokens', $removed)
      );
    }
  }

  /**
   * Gets all active devices that should receive a notification.
   *
   * @param NotificationType $type Notification type
   * @param int[] $user_ids Target user IDs
   * @return array Valid FCM tokens for notification
   */
  public function get_target_device_tokens(
    NotificationType $type,
    int $user_id
  ): array {
    $tokens = [];
    $user_devices = $this->token_repo->get_user_devices($user_id);
    foreach ($user_devices as $device) {
      if ($this->should_receive_notification($device, $type)) {
        $tokens[] = $device['token'];
      }
    }
    return $tokens;
  }

  /**
   * Checks if device should receive notification based on preferences.
   *
   * @param array $device Device data from repository
   * @param NotificationType $type Type of notification
   * @return bool Whether device should receive notification
   */
  private function should_receive_notification(
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
  public function handle_failed_delivery(string $token): void {
    $token = $this->token_repo->get(['token' => $token, 'single' => true]);

    if ($token) {
      $this->token_repo->delete($token->id);

      (new Utils())->log(
        sprintf(
          'Removed invalid FCM token for user %d device %s',
          $token->user_id,
          $token->device_id
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
  public function cleanup_inactive_tokens(int $days_threshold = 30): int {
    return $this->token_repo->delete_inactive_tokens($days_threshold);
  }

  /**
   * Syncs a device token - registers new device, updates token, or refreshes status.
   *
   * @param FCMToken $token Token to sync
   * @return array{token: FCMToken, created: bool} Token and whether it was newly created
   * @throws TokenRegistrationException|TokenUpdateException
   */
  public function sync_token(FCMToken $token): array {
    $existing_token = $this->token_repo->get([
      'user_id' => $token->user_id,
      'device_id' => $token->device_id,
      'single' => true,
    ]);

    if (!$existing_token) {
      $token = $this->register_new_device($token);
      return ['token' => $token, 'created' => true];
    }

    if ($this->should_update_token($existing_token, $token)) {
      $token = $this->update_token($existing_token, $token);
      return ['token' => $token, 'created' => false];
    }

    $token = $this->refresh_device_status($existing_token);
    return ['token' => $token, 'created' => false];
  }

  /**
   * Checks if device info needs updating.
   *
   * @param FCMToken $existing Existing device token
   * @param FCMToken $new New token data
   * @return bool Whether device needs updating
   */
  private function should_update_token(
    FCMToken $existing,
    FCMToken $new
  ): bool {
    return $existing->token !== $new->token ||
      $existing->device_name !== $new->device_name ||
      $existing->app_version !== $new->app_version;
  }

  /**
   * @throws TokenRegistrationException
   */
  private function register_new_device(FCMToken $token): FCMToken {
    $saved = $this->token_repo->add($token);
    if (!$saved) {
      throw new TokenRegistrationException('Failed to register device');
    }
    return $saved;
  }

  /**
   * @throws TokenUpdateException
   */
  private function update_token(FCMToken $existing, FCMToken $new): FCMToken {
    $updated = $this->token_repo->update_token($existing->id, [
      'token' => $new->token,
      'device_name' => $new->device_name,
      'app_version' => $new->app_version,
    ]);

    if (!$updated) {
      throw new TokenUpdateException('Failed to update device info');
    }
    return $updated;
  }

  /**
   * @throws TokenUpdateException
   */
  private function refresh_device_status(FCMToken $token): FCMToken {
    $updated = $this->token_repo->update_last_used($token->id);
    if (!$updated) {
      throw new TokenUpdateException('Failed to update status');
    }
    return $token;
  }
}
