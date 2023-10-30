<?php
require_once plugin_dir_path(dirname(__FILE__)) .
  'repository/class-wpbb-bracket-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'domain/class-wpbb-bracket.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'service/class-wpbb-score-service.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'service/class-wpbb-notification-service.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'service/class-wpbb-notification-service-interface.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wpbb-utils.php';

class Wpbb_BracketApi extends WP_REST_Controller {
  /**
   * @var Wpbb_BracketRepo
   */
  private $bracket_repo;

  /**
   * @var string
   */
  protected $namespace;

  /**
   * @var string
   */
  protected $rest_base;

  /**
   * @var Wpbb_ScoreService
   */
  private $score_service;

  /**
   * @var Wpbb_NotificationService_Interface
   */
  private ?Wpbb_NotificationService_Interface $notification_service;

  /**
   * @var Wpbb_Utils
   */
  private $utils;

  /**
   * Constructor.
   */
  public function __construct($args = []) {
    $this->utils = $args['utils'] ?? new Wpbb_Utils();
    $this->bracket_repo = new Wpbb_BracketRepo();
    $this->namespace = 'wp-bracket-builder/v1';
    $this->rest_base = 'brackets';
    $this->score_service = $args['score_service'] ?? new Wpbb_ScoreService();
    try {
      $this->notification_service =
        $args['notification_service'] ?? new Wpbb_NotificationService();
    } catch (Exception $e) {
      $this->notification_service = null;
    }
  }

  /**
   * Register the routes for bracket objects.
   * Adapted from: https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
   */
  public function register_routes() {
    $namespace = $this->namespace;
    $base = $this->rest_base;
    register_rest_route($namespace, '/' . $base, [
      [
        'methods' => WP_REST_Server::READABLE,
        'callback' => [$this, 'get_items'],
        'permission_callback' => [$this, 'admin_permission_check'],
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
        'permission_callback' => [$this, 'admin_permission_check'],
        'args' => [
          'context' => $this->get_context_param(['default' => 'view']),
        ],
      ],
      [
        'methods' => WP_REST_Server::EDITABLE,
        'callback' => [$this, 'update_item'],
        'permission_callback' => [$this, 'admin_permission_check'],
        'args' => $this->get_endpoint_args_for_item_schema(
          WP_REST_Server::EDITABLE
        ),
      ],
      [
        'methods' => WP_REST_Server::DELETABLE,
        'callback' => [$this, 'delete_item'],
        'permission_callback' => [$this, 'admin_permission_check'],
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
    register_rest_route($namespace, '/' . $base . '/(?P<id>[\d]+)/activate', [
      'methods' => 'POST',
      'callback' => [$this, 'activate_bracket'],
      'permission_callback' => [$this, 'admin_permission_check'],
      'args' => [
        'id' => [
          'description' => __('Unique identifier for the object.'),
          'type' => 'integer',
        ],
      ],
    ]);

    register_rest_route($namespace, '/' . $base . '/matches', [
      [
        'methods' => WP_REST_Server::READABLE,
        'callback' => [$this, 'get_matches'],
        'permission_callback' => [$this, 'customer_permission_check'],
        'args' => [],
      ],
    ]);

    register_rest_route($namespace, '/' . $base . '/teams', [
      [
        'methods' => WP_REST_Server::READABLE,
        'callback' => [$this, 'get_teams'],
        'permission_callback' => [$this, 'customer_permission_check'],
        'args' => [],
      ],
    ]);
  }

  /**
   * Retrieves a collection of brackets.
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_items($request) {
    $brackets = $this->bracket_repo->get_all();
    return new WP_REST_Response($brackets, 200);
  }

  /**
   * Retrieves a single bracket.
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_item($request) {
    // get id from request
    $id = $request->get_param('item_id');
    $bracket = $this->bracket_repo->get($id);
    return new WP_REST_Response($bracket, 200);
  }

  /**
   * Creates a single bracket.
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function create_item($request) {
    $params = $request->get_params();
    if (!isset($params['author'])) {
      $params['author'] = get_current_user_id();
    }
    if (!current_user_can('wpbb_share_bracket')) {
      $params['status'] = 'private';
    }
    try {
      $bracket = Wpbb_Bracket::from_array($params);
    } catch (Wpbb_ValidationException $e) {
      return new WP_Error('validation-error', $e->getMessage(), [
        'status' => 400,
      ]);
    }

    $saved = $this->bracket_repo->add($bracket);
    // check if user logged in
    if (!is_user_logged_in()) {
      // if (get_current_user_id() === 0)
      $this->utils->set_cookie('bracket_id', $saved->id);

      // nonce
      $this->utils->set_cookie('anonyous_bracket_nonce', 'fatty');
      update_post_meta($saved->post_id, 'anonymous_bracket', 'fatty');
    }
    // chec
    return new WP_REST_Response($saved, 201);
  }

  /**
   * Updates a single bracket.
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function update_item($request) {
    $bracket_id = $request->get_param('item_id');
    if (!current_user_can('wpbb_edit_bracket', $bracket_id)) {
      return new WP_Error(
        'not-authorized',
        'You are not authorized to edit this bracket.',
        ['status' => 403]
      );
    }
    if (!current_user_can('wpbb_share_bracket')) {
      $request['status'] = 'private';
      if (isset($request['status']) && $request['status'] === 'publish') {
        $request['status'] = 'private';
      }
      if (isset($request['results'])) {
        unset($request['results']);
      }
    }

    $data = $request->get_params();
    $updated = $this->bracket_repo->update($bracket_id, $data);

    $updated_results = $updated->results;
    $num_teams = $updated->num_teams;

    if (count($updated_results) > 0) {
      $old_status = $updated->status;
      if (count($updated_results) === $num_teams - 1) {
        $updated->status = 'complete';
      } else {
        $updated->status = 'score';
      }
      if ($old_status !== $updated->status) {
        $updated = $this->bracket_repo->update($bracket_id, [
          'status' => $updated->status,
        ]);
      }
      if (current_user_can('wpbb_share_bracket')) {
        $this->score_service->score_bracket_plays($updated);
        $notify = $request->get_param('update_notify_players');
        if ($this->notification_service && $notify) {
          $this->notification_service->notify_bracket_results_updated(
            $bracket_id
          );
        }
      }
    }

    return new WP_REST_Response($updated, 200);
  }

  /**
   * Deletes a single bracket.
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function delete_item($request) {
    // get id from request
    $id = $request->get_param('item_id');
    if (current_user_can('wpbb_delete_bracket', $id)) {
      $deleted = $this->bracket_repo->delete($id);
      return new WP_REST_Response($deleted, 200);
    } else {
      return new WP_Error(
        'not-authorized',
        'You are not authorized to delete this bracket.',
        ['status' => 403]
      );
    }
  }

  /**
   * Check if a given request has admin access to this plugin
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|bool
   */
  public function admin_permission_check($request) {
    return true;
    // return current_user_can('edit_others_posts');
  }

  /**
   * Check if a given request has customer access to this plugin. Anyone can view the data.
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|bool
   */
  public function customer_permission_check($request) {
    return true;
    // return current_user_can('read');
  }
}
