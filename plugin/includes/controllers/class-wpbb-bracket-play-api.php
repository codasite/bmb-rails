<?php
require_once plugin_dir_path(dirname(__FILE__)) .
  'repository/class-wpbb-bracket-play-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'domain/class-wpbb-bracket-play.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'service/image-generator/class-wpbb-local-node-generator.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wpbb-utils.php';

class Wpbb_BracketPlayApi extends WP_REST_Controller {
  /**
   * @var Wpbb_BracketPlayRepo
   */
  private $play_repo;

  /**
   * @var Wpbb_Utils
   */
  private $utils;

  // /**
  //  * @var Wpbb_Bracket_Pick_Service
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
   * @var Wpbb_BracketImageGeneratorInterface
   */
  private $image_generator;

  /**
   * Constructor.
   */
  public function __construct($args = []) {
    $this->utils = $args['utils'] ?? new Wpbb_Utils();
    $this->play_repo = $args['play_repo'] ?? new Wpbb_BracketPlayRepo();
    $this->image_generator =
      $args['image_generator'] ?? new Wpbb_LocalNodeGenerator();
    $this->namespace = 'wp-bracket-builder/v1';
    $this->rest_base = 'plays';
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
    register_rest_route($namespace, '/' . $base . '/html-to-image', [
      'methods' => 'POST',
      'callback' => [$this, 'html_to_image'],
      'permission_callback' => [$this, 'customer_permission_check'],
      'args' => [
        'id' => [
          'description' => __('Unique identifier for the object.'),
          'type' => 'integer',
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
      'post_type' => Wpbb_BracketPlay::get_post_type(),
      'post_status' => 'any',
    ]);

    $brackets = $this->play_repo->get_all($the_query);
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
    $bracket = $this->play_repo->get($id);
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
    try {
      $play = Wpbb_BracketPlay::from_array($params);
    } catch (Wpbb_ValidationException $e) {
      return new WP_Error('validation-error', $e->getMessage(), [
        'status' => 400,
      ]);
    }
    $saved = $this->play_repo->add($play);

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
