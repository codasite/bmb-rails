<?php
namespace WStrategies\BMB\Includes\Controllers;

use WP_Error;
use WP_Query;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Domain\ValidationException;
use WStrategies\BMB\Includes\Hooks\HooksInterface;
use WStrategies\BMB\Includes\Loader;
use WStrategies\BMB\Includes\Repository\BracketPlayRepo;
use WStrategies\BMB\Includes\Service\ProductIntegrations\Gelato\GelatoProductIntegration;
use WStrategies\BMB\Includes\Service\ProductIntegrations\ProductIntegrationInterface;
use WStrategies\BMB\Includes\Utils;

class BracketPlayApi extends WP_REST_Controller implements HooksInterface {
  /**
   * @var BracketPlayRepo
   */
  private $play_repo;

  /**
   * @var Utils
   */
  private $utils;

  // /**
  //  * @var Bracket_Pick_Service
  //  */
  // private $bracket_pick_service;

  /**
   * @var string
   */
  protected $namespace;

  /**
   * @var string
   */
  protected $rest_base;

  /**
   * @var ProductIntegrationInterface
   */
  private $product_integration;

  /**
   * Constructor.
   */
  public function __construct($args = []) {
    $this->utils = $args['utils'] ?? new Utils();
    $this->play_repo = $args['play_repo'] ?? new BracketPlayRepo();
    $this->product_integration =
      $args['product_integration'] ?? new GelatoProductIntegration();
    $this->namespace = 'wp-bracket-builder/v1';
    $this->rest_base = 'plays';
  }

  public function load(Loader $loader): void {
    $loader->add_action('rest_api_init', [$this, 'register_routes']);
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
        'args' => [
          'bracket_id' => [
            'description' => 'The ID of the bracket.',
            'type' => 'integer',
            'required' => false, // Set to true if the parameter is required
            'sanitize_callback' => 'absint', // Sanitize the input as an absolute integer value
            'validate_callback' => function ($param, $request, $key) {
              return is_numeric($param); // Validate that the input is a numeric value
            },
          ],
        ],
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
  }

  /**
   * Retrieves a collection of brackets.
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_items($request) {
    // $bracket_id = $request->get_param('bracket_id');
    $the_query = new WP_Query([
      'post_type' => BracketPlay::get_post_type(),
      'post_status' => 'any',
    ]);

    $plays = $this->play_repo->get_all($the_query);
    return new WP_REST_Response($plays, 200);
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
    $play = $this->play_repo->get($id);
    return new WP_REST_Response($play, 200);
  }

  /**
   * Creates a single bracket.
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function create_item($request) {
    $params = $request->get_params();
    $bracket_id = $params['bracket_id'];
    if (!current_user_can('wpbb_play_bracket', $bracket_id)) {
      return new WP_Error(
        'unauthorized',
        'You are not authorized to play this bracket.',
        ['status' => 403]
      );
    }
    if (isset($params['busted_id']) && $params['busted_id'] !== null) {
      $busted_play = $this->play_repo->get($params['busted_id']);
      if (!$busted_play->is_bustable) {
        return new WP_Error('unauthorized', 'This bracket cannot be busted.', [
          'status' => 403,
        ]);
      }
    }
    if (!isset($params['author'])) {
      $params['author'] = get_current_user_id();
    }
    if (has_tag('bmb_official', $bracket_id)) {
      $params['bmb_official'] = true;
    }
    try {
      $play = BracketPlay::from_array($params);
    } catch (ValidationException $e) {
      return new WP_Error('validation-error', $e->getMessage(), [
        'status' => 400,
      ]);
    }
    $saved = $this->play_repo->add($play);
    // Generate the bracket images
    if (
      isset($params['generate_images']) &&
      $params['generate_images'] === true
    ) {
      if (!$this->product_integration->has_all_configs()) {
        $this->product_integration->generate_images($saved);
      }
      // set the play id in the session
      $this->utils->set_cookie('play_id', $saved->id, ['days' => 30]);
    }

    // check if user logged in
    if (!is_user_logged_in()) {
      // if (get_current_user_id() === 0)
      $this->utils->set_cookie('play_id', $saved->id, ['days' => 30]);

      // nonce
      $bytes = random_bytes(32);
      $nonce = base64_encode($bytes);
      $this->utils->set_cookie('wpbb_anonymous_play_key', $nonce);

      update_post_meta($saved->id, 'wpbb_anonymous_play_key', $nonce);
    }

    return new WP_REST_Response($saved, 201);
  }

  /**
   * Check if a given request has admin access to this plugin
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|bool
   */
  public function admin_permission_check($request) {
    return true; // TODO: Disable this for production
    // return current_user_can('manage_options');
  }

  /**
   * Check if a given request has customer access to this plugin. Anyone can view the data.
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|bool
   */
  public function customer_permission_check($request) {
    return true; // TODO: Disable this for production
    // return current_user_can('read');
  }
}
