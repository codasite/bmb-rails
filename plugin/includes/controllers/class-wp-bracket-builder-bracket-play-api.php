<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-bracket-play-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-play.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'service/image-generator/class-wp-bracket-builder-local-node-generator.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wp-bracket-builder-utils.php';


class Wp_Bracket_Builder_Bracket_Play_Api extends WP_REST_Controller {

	/**
	 * @var Wp_Bracket_Builder_Bracket_Play_Repository
	 */
	private $play_repo;

	/**
	 * @var Wp_Bracket_Builder_Utils
	 */
	private $utils;

	// /**
	//  * @var Wp_Bracket_Builder_Bracket_Pick_Service
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
	 * @var Wp_Bracket_Builder_Post_Image_Generator_Interface
	 */
	private $image_generator;

	/**
	 * Constructor.
	 */
	// public function __construct(Wp_Bracket_Builder_Bracket_Repository_Interface $play_repo = null) {
	public function __construct() {
		// echo $play_repo;
		// $this->play_repo = $play_repo != null ? $play_repo : new Wp_Bracket_Builder_Bracket_Repository();
		$this->utils = new Wp_Bracket_Builder_Utils();
		$this->play_repo = new Wp_Bracket_Builder_Bracket_Play_Repository();
		$this->image_generator = new Wp_Bracket_Builder_Local_Node_Generator();
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
		register_rest_route($namespace, '/' . $base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array($this, 'get_items'),
				'permission_callback' => array($this, 'admin_permission_check'),
				'args'                => array(
					'bracket_id' => array(
						'description' => 'The ID of the bracket.',
						'type'        => 'integer',
						'required'    => false, // Set to true if the parameter is required
						'sanitize_callback' => 'absint', // Sanitize the input as an absolute integer value
						'validate_callback' => function ($param, $request, $key) {
							return is_numeric($param); // Validate that the input is a numeric value
						},
					),
				),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array($this, 'create_item'),
				'permission_callback' => array($this, 'customer_permission_check'),
				'args'                => $this->get_endpoint_args_for_item_schema(WP_REST_Server::CREATABLE),
			),
			'schema' => array($this, 'get_public_item_schema'),
		));
		register_rest_route($namespace, '/' . $base . '/(?P<item_id>[\d]+)', array(
			'args' => array(
				'item_id' => array(
					'description' => __('Unique identifier for the object.'),
					'type'        => 'integer',
				),
			),
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array($this, 'get_item'),
				'permission_callback' => array($this, 'customer_permission_check'),
				'args'                => array(
					'context' => $this->get_context_param(array('default' => 'view')),
				),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array($this, 'update_item'),
				'permission_callback' => array($this, 'admin_permission_check'),
				'args'                => $this->get_endpoint_args_for_item_schema(WP_REST_Server::EDITABLE),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array($this, 'delete_item'),
				'permission_callback' => array($this, 'admin_permission_check'),
				'args'                => array(
					'force' => array(
						'default'     => false,
						'description' => __('Required to be true, as resource does not support trashing.'),
						'type'        => 'boolean',
					),
				),
			),
		));
		register_rest_route($namespace, '/' . $base . '/html-to-image', array(
			'methods' => 'POST',
			'callback' => array($this, 'html_to_image'),
			'permission_callback' => array($this, 'customer_permission_check'),
			'args' => array(
				'id' => array(
					'description' => __('Unique identifier for the object.'),
					'type'        => 'integer',
				),
			),
		));
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
			'post_type' => Wp_Bracket_Builder_Bracket_Play::get_post_type(),
			'post_status' => 'any'
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
		$play = Wp_Bracket_Builder_Bracket_Play::from_array($params);
		$saved = $this->play_repo->add($play);

		return new WP_REST_Response($saved, 201);
		// return new WP_REST_Response($img_url, 201);
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
