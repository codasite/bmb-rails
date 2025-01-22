<?php

namespace WStrategies\BMB\Features\Notifications\Push;

use WStrategies\BMB\Includes\Utils;
use WStrategies\BMB\Features\Notifications\NotificationType;
use WStrategies\BMB\Includes\Hooks\HooksInterface;
use WStrategies\BMB\Includes\Hooks\Loader;
use WStrategies\BMB\Features\Notifications\Push\Exceptions\TokenRegistrationException;
use WStrategies\BMB\Features\Notifications\Push\Exceptions\TokenUpdateException;
use WStrategies\BMB\Features\Notifications\Push\Exceptions\TokenDeleteException;
use WStrategies\BMB\Features\Notifications\Push\Exceptions\TokenNotFoundException;
use WStrategies\BMB\Features\Notifications\Push\Exceptions\TokenDatabaseException;

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
   * Gets all active tokens that should receive a notification.
   *
   * @param NotificationType $type Notification type
   * @param int $user_id Target user ID
   * @return array Valid FCM token strings for notification
   */
  public function get_target_device_tokens(
    NotificationType $type,
    int $user_id
  ): array {
    try {
      $tokens = [];
      $user_tokens = $this->token_repo->get(['user_id' => $user_id]);
      if ($user_tokens) {
        foreach ($user_tokens as $token) {
          if ($this->should_receive_notification($token, $type)) {
            $tokens[] = $token->token;
          }
        }
      }
      return $tokens;
    } catch (TokenDatabaseException $e) {
      (new Utils())->log(
        sprintf(
          'Database error fetching tokens for user %d: %s',
          $user_id,
          $e->getMessage()
        )
      );
    } catch (\Exception $e) {
      (new Utils())->log(
        sprintf(
          'Unexpected error fetching tokens for user %d: %s',
          $user_id,
          $e->getMessage()
        )
      );
    }
    return [];
  }

  /**
   * Checks if device should receive notification based on preferences.
   *
   * @param FCMToken $token Token data from repository
   * @param NotificationType $type Type of notification
   * @return bool Whether device should receive notification
   */
  private function should_receive_notification(
    FCMToken $token,
    NotificationType $type
  ): bool {
    // Skip inactive devices (no activity in last 30 days)
    $last_used = strtotime($token->last_used_at);
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
    try {
      $token = $this->token_repo->get(['token' => $token, 'single' => true]);

      if ($token) {
        $deleted = $this->token_repo->delete($token->id);
        if (!$deleted) {
          (new Utils())->log(
            sprintf(
              'Failed to delete invalid token for user %d device %s',
              $token->user_id,
              $token->device_id
            )
          );
          return;
        }

        (new Utils())->log(
          sprintf(
            'Removed invalid FCM token for user %d device %s',
            $token->user_id,
            $token->device_id
          )
        );
      }
    } catch (TokenDatabaseException $e) {
      (new Utils())->log(
        sprintf(
          'Database error handling failed delivery for token %s: %s',
          $token,
          $e->getMessage()
        )
      );
    } catch (\Exception $e) {
      (new Utils())->log(
        sprintf(
          'Unexpected error handling failed delivery for token %s: %s',
          $token,
          $e->getMessage()
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
    try {
      return $this->token_repo->delete_inactive_tokens($days_threshold);
    } catch (TokenDatabaseException $e) {
      (new Utils())->log(
        sprintf(
          'Database error cleaning up inactive tokens: %s',
          $e->getMessage()
        )
      );
    } catch (\Exception $e) {
      (new Utils())->log(
        sprintf(
          'Unexpected error cleaning up inactive tokens: %s',
          $e->getMessage()
        )
      );
    }
    return 0;
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
      $token = $this->register_token($token);
      return ['token' => $token, 'created' => true];
    }

    if ($this->should_update_token($existing_token, $token)) {
      $token = $this->update_token($existing_token, $token);
      return ['token' => $token, 'created' => false];
    }

    $token = $this->refresh_token($existing_token);
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
  private function register_token(FCMToken $token): FCMToken {
    try {
      $saved = $this->token_repo->add($token);
      if (!$saved) {
        throw new TokenRegistrationException('Failed to register token');
      }
      return $saved;
    } catch (TokenDatabaseException $e) {
      (new Utils())->log(
        "Database error registering token: {$e->getMessage()}"
      );
      throw new TokenRegistrationException('Failed to register token');
    }
  }

  /**
   * @throws TokenUpdateException
   */
  private function update_token(FCMToken $existing, FCMToken $new): FCMToken {
    try {
      $updated = $this->token_repo->update_token($existing->id, [
        'token' => $new->token,
        'device_name' => $new->device_name,
        'app_version' => $new->app_version,
      ]);

      if (!$updated) {
        throw new TokenUpdateException('Failed to update token');
      }
      return $updated;
    } catch (TokenDatabaseException $e) {
      (new Utils())->log("Database error updating token: {$e->getMessage()}");
      throw new TokenUpdateException('Failed to update token');
    }
  }

  /**
   * @throws TokenUpdateException
   */
  private function refresh_token(FCMToken $token): FCMToken {
    try {
      $updated = $this->token_repo->update_token($token->id);
      if (!$updated) {
        throw new TokenUpdateException('Failed to refresh token');
      }
      return $token;
    } catch (TokenDatabaseException $e) {
      (new Utils())->log("Database error refreshing token: {$e->getMessage()}");
      throw new TokenUpdateException('Failed to refresh token');
    }
  }

  /**
   * Deregisters a device token.
   *
   * @param int $user_id User ID
   * @param string $device_id Device identifier
   * @throws TokenNotFoundException If device not found
   * @throws TokenDeleteException If deletion fails
   */
  public function deregister_token(int $user_id, string $device_id): void {
    try {
      $token = $this->token_repo->get([
        'user_id' => $user_id,
        'device_id' => $device_id,
        'single' => true,
      ]);

      if (!$token) {
        throw new TokenNotFoundException('Device token not found');
      }

      $deleted = $this->token_repo->delete($token->id);
      if (!$deleted) {
        throw new TokenDeleteException('Failed to delete device token');
      }
    } catch (TokenDatabaseException $e) {
      (new Utils())->log(
        "Database error deregistering token: {$e->getMessage()}"
      );
      throw new TokenDeleteException('Failed to delete device token');
    }
  }
}
