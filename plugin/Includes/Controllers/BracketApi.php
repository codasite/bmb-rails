<?php
namespace WStrategies\BMB\Includes\Controllers;

use Exception;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WStrategies\BMB\Includes\Domain\ValidationException;
use WStrategies\BMB\Includes\Hooks\HooksInterface;
use WStrategies\BMB\Includes\Loader;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Service\Notifications\BracketResultsNotificationService;
use WStrategies\BMB\Includes\Service\Notifications\BracketResultsNotificationServiceInterface;
use WStrategies\BMB\Includes\Service\ScoreService;
use WStrategies\BMB\Includes\Service\Serializer\BracketSerializer;
use WStrategies\BMB\Includes\Utils;

class BracketApi extends WP_REST_Controller implements HooksInterface {
  /**
   * @var BracketRepo
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
   * @var ScoreService
   */
  private $score_service;

  /**
   * @var BracketResultsNotificationServiceInterface
   */
  private ?BracketResultsNotificationServiceInterface $notification_service;

  /**
   * @var Utils
   */
  private $utils;

  private BracketSerializer $serializer;

  /**
   * Constructor.
   */
  public function __construct($args = []) {
    $this->utils = $args['utils'] ?? new Utils();
    $this->bracket_repo = new BracketRepo();
    $this->namespace = 'wp-bracket-builder/v1';
    $this->rest_base = 'brackets';
    $this->score_service = $args['score_service'] ?? new ScoreService();
    $this->serializer = $args['serializer'] ?? new BracketSerializer();
    try {
      $this->notification_service =
        $args['notification_service'] ??
        new BracketResultsNotificationService();
    } catch (Exception $e) {
      error_log(
        'Caught error: ' .
          $e->getMessage() .
          '\nSetting ' .
          __CLASS__ .
          '::$notification_service to null'
      );
      $this->notification_service = null;
    }
  }

  public function load(Loader $loader): void {
    $loader->add_action('rest_api_init', [$this, 'register_routes']);
  }

  /**
   * Register the routes for bracket objects.
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
        'permission_callback' => [$this, 'create_bracket_permission_check'],
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
   * Retrieves a collection of brackets.
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_items($request): WP_Error|WP_REST_Response {
    $brackets = $this->bracket_repo->get_all();
    $serialized = [];
    foreach ($brackets as $bracket) {
      $serialized[] = $this->serializer->serialize($bracket);
    }
    return new WP_REST_Response($serialized, 200);
  }

  /**
   * Retrieves a single bracket.
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_item($request): WP_Error|WP_REST_Response {
    // get id from request
    $id = $request->get_param('item_id');
    $bracket = $this->bracket_repo->get($id);
    $serialized = $this->serializer->serialize($bracket);
    return new WP_REST_Response($serialized, 200);
  }

  /**
   * Creates a single bracket.
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function create_item($request): WP_Error|WP_REST_Response {
    $params = $request->get_params();
    try {
      $bracket = $this->serializer->deserialize($params);
    } catch (ValidationException $e) {
      return new WP_Error('validation-error', $e->getMessage(), [
        'status' => 400,
      ]);
    }
    $bracket->author = get_current_user_id();
    $bracket->status = 'private';

    try {
      $saved = $this->bracket_repo->add($bracket);
    } catch (Exception $e) {
      return new WP_Error('server-error', $e->getMessage(), [
        'status' => 500,
      ]);
    }
    // check if user logged in
    if (!is_user_logged_in()) {
      // if (get_current_user_id() === 0)
      $this->utils->set_cookie('wpbb_anonymous_bracket_id', $saved->id);

      // nonce
      // $nonce = 'fatty';
      $bytes = random_bytes(32);
      $nonce = base64_encode($bytes);
      $this->utils->set_cookie('wpbb_anonymous_bracket_key', $nonce);

      update_post_meta($saved->id, 'wpbb_anonymous_bracket_key', $nonce);
    }
    $serialized = $this->serializer->serialize($saved);
    return new WP_REST_Response($serialized, 201);
  }

  /**
   * Updates a single bracket.
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function update_item($request): WP_Error|WP_REST_Response {
    $can_set_results = false;
    $set_winners = false;
    $bracket_id = $request->get_param('item_id');
    if (!current_user_can('wpbb_edit_bracket', $bracket_id)) {
      return new WP_Error(
        'not-authorized',
        'You are not authorized to edit this bracket.',
        ['status' => 403]
      );
    }
    if (current_user_can('wpbb_share_bracket')) {
      $can_set_results = true;
    } else {
      $request->set_param('status', 'private');
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
        $set_winners = true;
      } else {
        $updated->status = 'score';
      }
      if ($old_status !== $updated->status) {
        $updated = $this->bracket_repo->update($bracket_id, [
          'status' => $updated->status,
        ]);
      }
      if ($can_set_results) {
        $this->score_service->score_bracket_plays($updated, $set_winners);
        $notify = $request->get_param('update_notify_players');
        if ($this->notification_service && $notify) {
          $this->notification_service->notify_bracket_results_updated(
            $bracket_id
          );
        }
      }
    }
    $serialized = $this->serializer->serialize($updated);
    return new WP_REST_Response($serialized, 200);
  }

  /**
   * Deletes a single bracket.
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function delete_item($request): WP_Error|WP_REST_Response {
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

  public function create_bracket_permission_check(
    WP_REST_Request $request
  ): WP_Error|bool {
    return true;
  }
}
