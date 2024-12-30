<?php

namespace WStrategies\BMB\Features\Notifications\Push;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WStrategies\BMB\Includes\Domain\ValidationException;
use WStrategies\BMB\Includes\Hooks\HooksInterface;
use WStrategies\BMB\Includes\Hooks\Loader;

class FCMTokenApi extends WP_REST_Controller implements HooksInterface {
  private FCMTokenRepo $token_repo;
  protected string $api_namespace;
  protected string $api_rest_base;

  public function __construct($args = []) {
    $this->api_namespace = 'bmb/v1';
    $this->api_rest_base = 'fcm';
    $this->token_repo = $args['token_repo'] ?? new FCMTokenRepo();
  }

  public function load(Loader $loader): void {
    $loader->add_action('rest_api_init', [$this, 'register_routes']);
  }

  public function register_routes(): void {
    $namespace = $this->api_namespace;
    $base = $this->api_rest_base;

    // Register device token
    register_rest_route($namespace, "/{$base}/register", [
      'methods' => WP_REST_Server::CREATABLE,
      'callback' => [$this, 'register_token'],
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
          'required' => true,
          'type' => 'string',
          'enum' => ['ios', 'android'],
        ],
        'app_version' => [
          'type' => 'string',
        ],
      ],
    ]);

    // Update token
    register_rest_route($namespace, "/{$base}/update", [
      'methods' => WP_REST_Server::EDITABLE,
      'callback' => [$this, 'update_token'],
      'permission_callback' => [$this, 'permission_check'],
      'args' => [
        'old_token' => [
          'required' => true,
          'type' => 'string',
        ],
        'new_token' => [
          'required' => true,
          'type' => 'string',
        ],
        'device_id' => [
          'required' => true,
          'type' => 'string',
        ],
      ],
    ]);

    // Deregister device
    register_rest_route($namespace, "/{$base}/deregister", [
      'methods' => WP_REST_Server::DELETABLE,
      'callback' => [$this, 'deregister_token'],
      'permission_callback' => [$this, 'permission_check'],
      'args' => [
        'device_id' => [
          'required' => true,
          'type' => 'string',
        ],
      ],
    ]);

    // Update device status
    register_rest_route($namespace, "/{$base}/status", [
      'methods' => WP_REST_Server::CREATABLE,
      'callback' => [$this, 'update_status'],
      'permission_callback' => [$this, 'permission_check'],
      'args' => [
        'device_id' => [
          'required' => true,
          'type' => 'string',
        ],
        'status' => [
          'required' => true,
          'type' => 'string',
          'enum' => ['active'],
        ],
      ],
    ]);
  }

  public function register_token(
    WP_REST_Request $request
  ): WP_Error|WP_REST_Response {
    try {
      $user_id = get_current_user_id();
      $params = $request->get_params();

      $token = FCMTokenFactory::create([
        'user_id' => $user_id,
        'device_id' => $params['device_id'],
        'token' => $params['token'],
        'device_type' => $params['platform'],
        'device_name' => $params['device_name'] ?? null,
        'app_version' => $params['app_version'] ?? null,
      ]);

      $saved = $this->token_repo->add(
        $token->user_id,
        $token->device_id,
        $token->token,
        $token->device_type,
        $token->device_name,
        $token->app_version
      );

      if ($saved) {
        $saved['id'] = (int) $saved['id'];
      }

      return new WP_REST_Response($saved, 201);
    } catch (ValidationException $e) {
      return new WP_Error('validation_error', $e->getMessage(), [
        'status' => 400,
      ]);
    }
  }

  public function update_token(
    WP_REST_Request $request
  ): WP_Error|WP_REST_Response {
    $user_id = get_current_user_id();
    $params = $request->get_params();

    // Find existing token
    $existing = $this->token_repo->get([
      'user_id' => $user_id,
      'device_id' => $params['device_id'],
      'token' => $params['old_token'],
      'single' => true,
    ]);

    if (!$existing) {
      return new WP_Error(
        'token_not_found',
        'Token not found for this device',
        ['status' => 404]
      );
    }

    $updated = $this->token_repo->update_token(
      $existing['id'],
      $params['new_token']
    );
    return new WP_REST_Response($updated, 200);
  }

  public function deregister_token(
    WP_REST_Request $request
  ): WP_Error|WP_REST_Response {
    $user_id = get_current_user_id();
    $device_id = $request->get_param('device_id');

    // Check if device exists first
    $device = $this->token_repo->get([
      'user_id' => $user_id,
      'device_id' => $device_id,
      'single' => true,
    ]);

    if (!$device) {
      return new WP_Error('device_not_found', 'Device not found', [
        'status' => 404,
      ]);
    }

    $deleted = $this->token_repo->delete_by_device($user_id, $device_id);
    if (!$deleted) {
      return new WP_Error('delete_failed', 'Failed to delete device token', [
        'status' => 500,
      ]);
    }

    return new WP_REST_Response(['success' => true], 200);
  }

  public function update_status(
    WP_REST_Request $request
  ): WP_Error|WP_REST_Response {
    $user_id = get_current_user_id();
    $device_id = $request->get_param('device_id');

    // Find the token for this device
    $device = $this->token_repo->get([
      'user_id' => $user_id,
      'device_id' => $device_id,
      'single' => true,
    ]);

    if (!$device) {
      return new WP_Error('device_not_found', 'Device not found', [
        'status' => 404,
      ]);
    }

    $updated = $this->token_repo->update_last_used($device['token']);
    if (!$updated) {
      return new WP_Error('update_failed', 'Failed to update device status', [
        'status' => 500,
      ]);
    }

    return new WP_REST_Response(['success' => true], 200);
  }

  public function permission_check(WP_REST_Request $request): bool {
    return is_user_logged_in();
  }
}
