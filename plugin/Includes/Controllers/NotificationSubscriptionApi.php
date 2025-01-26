<?php
namespace WStrategies\BMB\Includes\Controllers;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WStrategies\BMB\Features\Notifications\Infrastructure\NotificationSubscriptionRepo;
use WStrategies\BMB\Includes\Domain\ValidationException;
use WStrategies\BMB\Includes\Factory\NotificationFactory;
use WStrategies\BMB\Includes\Hooks\HooksInterface;
use WStrategies\BMB\Includes\Hooks\Loader;

class NotificationSubscriptionApi extends WP_REST_Controller implements
  HooksInterface {
  /**
   * @var NotificationSubscriptionRepo
   */
  private $notification_sub_repo;

  /**
   * @var string
   */
  protected $namespace;

  /**
   * @var string
   */
  protected $rest_base;

  /**
   * Constructor.
   */
  public function __construct($args = []) {
    $this->namespace = 'wp-bracket-builder/v1';
    $this->rest_base = 'notifications';
    $this->notification_sub_repo =
      $args['notification_sub_repo'] ?? new NotificationSubscriptionRepo();
  }

  public function load(Loader $loader): void {
    $loader->add_action('rest_api_init', [$this, 'register_routes']);
  }

  /**
   * Register the routes for notification objects.
   * Adapted from: https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
   */
  public function register_routes(): void {
    $namespace = $this->namespace;
    $base = $this->rest_base;
    register_rest_route($namespace, '/' . $base, [
      [
        'methods' => WP_REST_Server::READABLE,
        'callback' => [$this, 'get_items'],
        'permission_callback' => [$this, 'customer_permission_check'],
        'args' => [],
      ],
      [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => [$this, 'create_item'],
        'permission_callback' => [$this, 'customer_permission_check'],
        'args' => $this->get_endpoint_args_for_item_schema(
          WP_REST_Server::CREATABLE
        ),
      ],
      'schema' => [$this, 'get_public_item_schema'],
    ]);
    register_rest_route($namespace, '/' . $base . '/(?P<item_id>[\d]+)', [
      'args' => [
        'item_id' => [
          'description' => __('Unique identifier for the object.'),
          'type' => 'integer',
        ],
      ],
      [
        'methods' => WP_REST_Server::READABLE,
        'callback' => [$this, 'get_item'],
        'permission_callback' => [$this, 'customer_permission_check'],
        'args' => [
          'context' => $this->get_context_param(['default' => 'view']),
        ],
      ],
      [
        'methods' => WP_REST_Server::EDITABLE,
        'callback' => [$this, 'update_item'],
        'permission_callback' => [$this, 'customer_permission_check'],
        'args' => $this->get_endpoint_args_for_item_schema(
          WP_REST_Server::EDITABLE
        ),
      ],
      [
        'methods' => WP_REST_Server::DELETABLE,
        'callback' => [$this, 'delete_item'],
        'permission_callback' => [$this, 'customer_permission_check'],
        'args' => [
          'force' => [
            'default' => false,
            'description' => __(
              'Required to be true, as resource does not support trashing.'
            ),
            'type' => 'boolean',
          ],
        ],
      ],
    ]);
  }

  /**
   * Retrieves a collection of notifications.
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_items($request): WP_Error|WP_REST_Response {
    $notifications = $this->notification_sub_repo->get();
    return new WP_REST_Response($notifications, 200);
  }

  /**
   * Retrieves a single notification.
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_item($request): WP_Error|WP_REST_Response {
    // get id from request
    $id = $request->get_param('item_id');
    $notification = $this->notification_sub_repo->get([
      'id' => $id,
      'single' => true,
    ]);
    if ($notification) {
      return new WP_REST_Response($notification, 200);
    } else {
      return new WP_Error('not-found', 'Notification not found.', [
        'status' => 404,
      ]);
    }
  }

  /**
   * Creates a single notification.
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function create_item($request): WP_Error|WP_REST_Response {
    $params = $request->get_params();
    if (!isset($params['user_id'])) {
      $params['user_id'] = get_current_user_id();
    }
    try {
      $notification = NotificationFactory::create($params);
      $saved = $this->notification_sub_repo->add($notification);
      return new WP_REST_Response($saved, 201);
    } catch (ValidationException $e) {
      return new WP_Error('validation-error', $e->getMessage(), [
        'status' => 400,
      ]);
    }
  }

  /**
   * Deletes a single notification.
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function delete_item($request): WP_Error|WP_REST_Response {
    // get id from request
    $id = $request->get_param('item_id');
    if (current_user_can('wpbb_delete_notification', $id)) {
      $deleted = $this->notification_sub_repo->delete($id);
      return new WP_REST_Response($deleted, 200);
    } else {
      return new WP_Error(
        'not-authorized',
        'You are not authorized to delete this notification.',
        ['status' => 403]
      );
    }
  }

  /**
   * Check if a given request has customer access to this plugin. Anyone can view the data.
   *
   * @param WP_REST_Request $request Full details about the request.
   *
   * @return WP_Error|bool
   */
  public function customer_permission_check(
    WP_REST_Request $request
  ): WP_Error|bool {
    return current_user_can('read');
  }
}
