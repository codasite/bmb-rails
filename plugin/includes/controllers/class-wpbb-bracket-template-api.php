<?php
require_once plugin_dir_path(dirname(__FILE__)) .
  'repository/class-wpbb-bracket-template-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'domain/class-wpbb-bracket-template.php';
// require_once plugin_dir_path(dirname(__FILE__)) . 'validations/class-wpbb-bracket-api-validation.php';

class Wpbb_BracketTemplateApi extends WP_REST_Controller {
  /**
   * @var Wpbb_BracketTemplateRepo
   */
  private $template_repo;

  /**
   * @var Wpbb_ApiValidation
   */
  private $bracket_validate;

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
  public function __construct() {
    $this->template_repo = new Wpbb_BracketTemplateRepo();
    $this->namespace = 'wp-bracket-builder/v1';
    $this->rest_base = 'templates';
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
   * Retrieves a collection of templates.
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_items($request) {
    $templates = $this->template_repo->get_all();
    return new WP_REST_Response($templates, 200);
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
    $template = $this->template_repo->get($id);
    return new WP_REST_Response($template, 200);
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
      $template = Wpbb_BracketTemplate::from_array($params);
    } catch (Wpbb_ValidationException $e) {
      return new WP_Error('validation-error', $e->getMessage(), [
        'status' => 400,
      ]);
    }

    $saved = $this->template_repo->add($template);
    return new WP_REST_Response($saved, 201);
  }

  /**
   * Updates a single bracket.
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function update_item($request) {
    $data = $request->get_params();
    $updated = $this->template_repo->update(
      $request->get_param('item_id'),
      $data
    );
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
    $deleted = $this->template_repo->delete($id);
    return new WP_REST_Response($deleted, 200);
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
