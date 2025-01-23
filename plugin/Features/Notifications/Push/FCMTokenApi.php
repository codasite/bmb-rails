<?php

namespace WStrategies\BMB\Features\Notifications\Push;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WStrategies\BMB\Includes\Hooks\HooksInterface;
use WStrategies\BMB\Includes\Hooks\Loader;
use Exception;
use WStrategies\BMB\Features\Notifications\Push\Exceptions\TokenRegistrationException;
use WStrategies\BMB\Features\Notifications\Push\Exceptions\TokenUpdateException;
use WStrategies\BMB\Features\Notifications\Push\Exceptions\TokenDeleteException;
use WStrategies\BMB\Features\Notifications\Push\Exceptions\TokenNotFoundException;

/**
 * REST API controller for managing FCM tokens.
 *
 * Provides endpoints for managing the lifecycle of Firebase Cloud Messaging (FCM) tokens
 * used for push notifications. All endpoints require authentication.
 *
 * Token Lifecycle:
 * 1. Initial Registration: When app is first installed or user logs in
 * 2. Token Refresh: When Firebase issues a new token
 * 3. Status Updates: Regular pings to keep token active
 * 4. Deregistration: When user logs out or uninstalls app
 *
 * Available Endpoints:
 * - POST /bmb/v1/fcm/register - Initial device registration
 * - PUT /bmb/v1/fcm/update - Handle Firebase token refresh
 * - DELETE /bmb/v1/fcm/deregister - Clean up on logout/uninstall
 * - POST /bmb/v1/fcm/status - Keep token active
 *
 * Example Usage:
 * ```js
 * // 1. Register when app starts
 * await fetch('/wp-json/bmb/v1/fcm/register', {
 *   method: 'POST',
 *   headers: {
 *     'Content-Type': 'application/json',
 *     'X-WP-Nonce': wpNonce
 *   },
 *   body: JSON.stringify({
 *     token: 'fcm-token-123',
 *     device_id: 'device-123',
 *     platform: 'ios',
 *     device_name: 'iPhone 12',
 *     app_version: '1.0.0'
 *   })
 * });
 *
 * // 2. Update when Firebase refreshes token
 * messaging.onTokenRefresh(() => {
 *   const newToken = await messaging.getToken();
 *   await fetch('/wp-json/bmb/v1/fcm/update', {
 *     method: 'PUT',
 *     // ... headers ...
 *     body: JSON.stringify({
 *       old_token: currentToken,
 *       new_token: newToken,
 *       device_id: deviceId
 *     })
 *   });
 * });
 *
 * // 3. Regular status updates
 * setInterval(async () => {
 *   await fetch('/wp-json/bmb/v1/fcm/status', {
 *     method: 'POST',
 *     // ... headers ...
 *     body: JSON.stringify({
 *       device_id: deviceId,
 *       status: 'active'
 *     })
 *   });
 * }, 24 * 60 * 60 * 1000); // Daily
 *
 * // 4. Cleanup on logout
 * async function logout() {
 *   await fetch('/wp-json/bmb/v1/fcm/deregister', {
 *     method: 'DELETE',
 *     // ... headers ...
 *     body: JSON.stringify({ device_id: deviceId })
 *   });
 * }
 * ```
 */
class FCMTokenApi extends WP_REST_Controller implements HooksInterface {
  /** @var FCMTokenRepo Repository for token storage and retrieval */
  private FCMTokenRepo $token_repo;

  /** @var FCMTokenManager Token manager for token operations */
  private FCMTokenManager $token_manager;

  /** @var string The API namespace */
  private string $api_namespace;

  /** @var string The API endpoint base */
  private string $api_rest_base;

  /**
   * Initializes the API controller.
   *
   * @param array $args {
   *     Optional. Arguments for initializing the controller.
   *     @type FCMTokenRepo $token_repo Token repository instance.
   *     @type FCMTokenManager $token_manager Token manager instance.
   * }
   */
  public function __construct($args = []) {
    $this->api_namespace = 'bmb/v1';
    $this->api_rest_base = 'fcm/token';
    $this->token_repo = $args['token_repo'] ?? new FCMTokenRepo();
    $this->token_manager = $args['token_manager'] ?? new FCMTokenManager();
  }

  /**
   * Registers WordPress hooks.
   *
   * @param Loader $loader WordPress hook loader.
   */
  public function load(Loader $loader): void {
    $loader->add_action('rest_api_init', [$this, 'register_routes']);
  }

  /**
   * Registers the REST API routes.
   *
   * Endpoint Documentation:
   *
   * 1. Register Device Token
   *    Endpoint: POST /bmb/v1/fcm/register
   *    When to Use:
   *    - On first app install
   *    - After user logs in
   *    - If no existing token is found
   *    Required Fields:
   *    - token (string): The FCM token from Firebase
   *    - device_id (string): Unique device identifier
   *    - platform (string): Either 'ios' or 'android'
   *    Optional Fields:
   *    - device_name (string): Human-readable device name
   *    - app_version (string): Version of the mobile app
   *    Returns: 201 Created with token details
   *    Notes:
   *    - Will update existing token if device_id exists
   *    - Starts the token lifecycle
   *
   * 2. Update Token
   *    Endpoint: PUT /bmb/v1/fcm/update
   *    When to Use:
   *    - When Firebase refreshes the token
   *    - If token validation fails
   *    Required Fields:
   *    - old_token (string): Current FCM token
   *    - new_token (string): New FCM token
   *    - device_id (string): Device identifier
   *    Returns: 200 OK with updated token or 404 Not Found
   *    Notes:
   *    - Maintains the same device registration
   *    - Updates last_used_at timestamp
   *
   * 3. Deregister Device
   *    Endpoint: DELETE /bmb/v1/fcm/deregister
   *    When to Use:
   *    - User logs out
   *    - App is uninstalled
   *    - User revokes push permission
   *    Required Fields:
   *    - device_id (string): Device to deregister
   *    Returns: 200 OK on success or 404 Not Found
   *    Notes:
   *    - Completely removes the token
   *    - User will need to re-register for pushes
   *
   * 4. Update Status
   *    Endpoint: POST /bmb/v1/fcm/status
   *    When to Use:
   *    - Periodic health check (e.g., daily)
   *    - After app comes to foreground
   *    - To prevent token expiration
   *    Required Fields:
   *    - device_id (string): Device identifier
   *    - status (string): Must be 'active'
   *    Returns: 200 OK on success or 404 Not Found
   *    Notes:
   *    - Updates last_used_at timestamp
   *    - Prevents cleanup of active tokens
   *    - Should be called regularly
   *
   * Token Cleanup:
   * - Tokens inactive for 30+ days are automatically removed
   * - User tokens are removed on account deletion
   * - One device can only have one active token
   */
  public function register_routes(): void {
    $namespace = $this->api_namespace;
    $base = $this->api_rest_base;

    // Sync token
    register_rest_route($namespace, "/{$base}/sync", [
      'methods' => WP_REST_Server::CREATABLE,
      'callback' => [$this, 'sync_token'],
      'permission_callback' => [$this, 'permission_check'],
      'args' => [
        'token' => [
          'required' => true,
          'type' => 'string',
        ],
        'device_id' => [
          'required' => true,
          'type' => 'string',
        ],
        'platform' => [
          'type' => 'string',
          'enum' => ['ios', 'android'],
        ],
        'device_name' => [
          'type' => 'string',
        ],
        'app_version' => [
          'type' => 'string',
        ],
      ],
    ]);

    // Delete token
    register_rest_route($namespace, "/{$base}", [
      'methods' => WP_REST_Server::DELETABLE,
      'callback' => [$this, 'delete_token'],
      'permission_callback' => [$this, 'permission_check'],
      'args' => [
        'device_id' => [
          'required' => true,
          'type' => 'string',
        ],
      ],
    ]);
  }

  /**
   * Syncs device token - registers new device, updates token, or refreshes status.
   *
   * Example Request:
   * ```json
   * POST /wp-json/bmb/v1/fcm/sync
   * {
   *   "token": "fcm-token-123",
   *   "device_id": "device-123",
   *   "platform": "ios",
   *   "device_name": "iPhone 12",
   *   "app_version": "1.0.0"
   * }
   * ```
   */
  public function sync_token(
    WP_REST_Request $request
  ): WP_Error|WP_REST_Response {
    try {
      $params = $request->get_params();
      $token = FCMTokenFactory::create([
        'user_id' => get_current_user_id(),
        'device_id' => $params['device_id'],
        'token' => $params['token'],
        'device_type' => $params['platform'],
        'device_name' => $params['device_name'] ?? null,
        'app_version' => $params['app_version'] ?? null,
      ]);

      $result = $this->token_manager->sync_token($token);
      return new WP_REST_Response(
        $result['token']->to_array(),
        $result['created'] ? 201 : 200
      );
    } catch (TokenRegistrationException $e) {
      return new WP_Error('registration_failed', $e->getMessage(), [
        'status' => 500,
      ]);
    } catch (TokenUpdateException $e) {
      return new WP_Error('update_failed', $e->getMessage(), [
        'status' => 500,
      ]);
    } catch (Exception $e) {
      return new WP_Error('sync_failed', $e->getMessage(), ['status' => 500]);
    }
  }

  /**
   * Deregisters a device token.
   *
   * Example Request:
   * ```json
   * DELETE /wp-json/bmb/v1/fcm/deregister
   * {
   *   "device_id": "device-123"
   * }
   * ```
   *
   * @param WP_REST_Request $request The deregistration request.
   * @return WP_REST_Response|WP_Error Response or error.
   */
  public function delete_token(
    WP_REST_Request $request
  ): WP_Error|WP_REST_Response {
    try {
      $this->token_manager->deregister_token(
        get_current_user_id(),
        $request->get_param('device_id')
      );
      return new WP_REST_Response(['success' => true], 200);
    } catch (TokenNotFoundException $e) {
      return new WP_Error('device_not_found', $e->getMessage(), [
        'status' => 404,
      ]);
    } catch (TokenDeleteException $e) {
      return new WP_Error('delete_failed', $e->getMessage(), [
        'status' => 500,
      ]);
    }
  }

  /**
   * Checks if the current user has permission to access endpoints.
   *
   * @param WP_REST_Request $request The incoming request.
   * @return bool True if user is logged in, false otherwise.
   */
  public function permission_check(WP_REST_Request $request): bool {
    return is_user_logged_in();
  }
}
