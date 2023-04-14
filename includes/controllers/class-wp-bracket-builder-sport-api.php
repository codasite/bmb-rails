<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-sport-repo.php';

class Wp_Bracket_Builder_Sport_Api extends WP_REST_Controller {

	/**
	 * @var Wp_Bracket_Builder_Sport_Repo
	 */
	private $sport_repo;

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
	public function __construct(Wp_Bracket_Builder_Sport_Repository_Interface $sport_repo = null) {
		$this->sport_repo = $sport_repo != null ? $sport_repo : new Wp_Bracket_Builder_Sport_Repository();
		$this->namespace = 'wp-bracket-builder/v1';
		$this->rest_base = 'sports';
	}

	/**
	 * Register the routes for sport objects.
	 * Adapted from: https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
	 */
	public function register_routes() {
		$version = '1';
		$namespace = $this->namespace;
		$base = $this->rest_base;
		register_rest_route($namespace, '/' . $base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array($this, 'get_items'),
				'permission_callback' => array($this, 'get_items_permissions_check'),
				'args'                => array(),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array($this, 'create_item'),
				'permission_callback' => array($this, 'create_item_permissions_check'),
				'args'                => $this->get_endpoint_args_for_item_schema(WP_REST_Server::CREATABLE),
			),
			'schema' => array($this, 'get_public_item_schema'),
		));
		register_rest_route($namespace, '/' . $base . '/(?P<id>[\d]+)', array(
			'args' => array(
				'id' => array(
					'description' => __('Unique identifier for the object.'),
					'type'        => 'integer',
				),
			),
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array($this, 'get_item'),
				'permission_callback' => array($this, 'get_item_permissions_check'),
				'args'                => array(
					'context' => $this->get_context_param(array('default' => 'view')),
				),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array($this, 'update_item'),
				'permission_callback' => array($this, 'update_item_permissions_check'),
				'args'                => $this->get_endpoint_args_for_item_schema(WP_REST_Server::EDITABLE),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array($this, 'delete_item'),
				'permission_callback' => array($this, 'delete_item_permissions_check'),
				'args'                => array(
					'force' => array(
						'default'     => false,
						'description' => __('Required to be true, as resource does not support trashing.'),
						'type'        => 'boolean',
					),
				),
			),
		));
	}

	/**
	 * Check if a given request has access to read sports.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check($request) {
		return true;
		// return current_user_can('manage_bracket_builder');
	}

	/**
	 * Retrieves a collection of sports.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items($request) {
		$sports = $this->sport_repo->get_all();
		return new WP_REST_Response($sports, 200);
	}

	/**
	 * Check if a given request has access to read a sport.
	 * 
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|bool
	 */
	public function get_item_permissions_check($request) {
		$data = array();
		return new WP_REST_Response($data, 200);
	}

	/**
	 * Retrieves a single sport.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item($request) {
		return array();
	}

	/**
	 * Check if a given request has access to create a sport.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|bool
	 */
	public function create_item_permissions_check($request) {
		return true;
	}

	/**
	 * Creates a single sport.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item($request) {
		return new WP_Error('cant-create', __('message', 'text-domain'), array('status' => 500));
	}

	/**
	 * Check if a given request has access to update a sport.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|bool
	 */
	public function update_item_permissions_check($request) {
		return true;
	}

	/**
	 * Updates a single sport.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item($request) {
		return new WP_Error('cant-update', __('message', 'text-domain'), array('status' => 500));
	}

	/**
	 * Check if a given request has access to delete a sport.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|bool
	 */

	public function delete_item_permissions_check($request) {
		return true;
	}

	/**
	 * Deletes a single sport.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_item($request) {
		return new WP_Error('cant-delete', __('message', 'text-domain'), array('status' => 500));
	}

	/**
	 * Prepares the item for create or update operation.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_Error|object $prepared_item
	 */
	protected function prepare_item_for_database($request) {
		return array();
	}

	/**
	 * Prepares the item for the REST response.
	 *
	 * @param object          $item    WordPress representation of the item.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response $response
	 */
	public function prepare_item_for_response($item, $request) {
		return array();
	}
}
