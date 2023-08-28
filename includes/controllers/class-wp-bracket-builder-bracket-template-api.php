<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-bracket-template-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-template.php';
// require_once plugin_dir_path(dirname(__FILE__)) . 'validations/class-wp-bracket-builder-bracket-api-validation.php';

class Wp_Bracket_Builder_Bracket_Template_Api extends WP_REST_Controller {

	/**
	 * @var Wp_Bracket_Builder_Bracket_Repo
	 */
	private $template_repo;

	/**
	 * @var Wp_Bracket_Builder_Bracket_Api_Validation
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
	// public function __construct(Wp_Bracket_Builder_Bracket_Repository_Interface $bracket_repo = null) {
	public function __construct() {
		// echo $bracket_repo;
		// $this->bracket_repo = $bracket_repo != null ? $bracket_repo : new Wp_Bracket_Builder_Bracket_Repository();
		$this->template_repo = new Wp_Bracket_Builder_Bracket_Template_Repository();
		$this->namespace = 'wp-bracket-builder/v1';
		$this->rest_base = 'templates';
		// $this->bracket_validate = new Wp_Bracket_Builder_Bracket_Api_Validation();
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
				'args'                => array(),
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
				'permission_callback' => array($this, 'admin_permission_check'),
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
		register_rest_route($namespace, '/' . $base . '/(?P<id>[\d]+)/activate', array(
			'methods' => 'POST',
			'callback' => array($this, 'activate_bracket'),
			'permission_callback' => array($this, 'admin_permission_check'),
			'args' => array(
				'id' => array(
					'description' => __('Unique identifier for the object.'),
					'type'        => 'integer',
				),
			),
		));

		register_rest_route($namespace, '/' . $base . '/(?P<id>[\d]+)/deactivate', array(
			'methods' => 'POST',
			'callback' => array($this, 'deactivate_bracket'),
			'permission_callback' => array($this, 'admin_permission_check'),
			'args' => array(
				'id' => array(
					'description' => __('Unique identifier for the object.'),
					'type'        => 'integer',
				),
			),
		));

		register_rest_route($namespace, '/' . $base . '/get-user-brackets', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array($this, 'get_user_items'),
				'permission_callback' => array($this, 'customer_permission_check'),
				'args'                => array(),
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
		$brackets = $this->template_repo->get_all();
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
		$bracket = $this->template_repo->get($id);
		return new WP_REST_Response($bracket, 200);
	}

	/**
	 * Creates a single bracket.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item($request) {
		$template = Wp_Bracket_Builder_Bracket_Template::from_array($request->get_params());

		//checking validation for requested data
		// $validated = $this->bracket_validate->validate_bracket_api($template);
		// if (!isset($validated)) {

		$saved = $this->template_repo->add($template);
		return new WP_REST_Response($saved, 201);
		// return new WP_REST_Response($template, 201);

		// }
		// return $validated;
		// return new WP_Error('cant-create', __('message', 'text-domain'), array('status' => 500));

	}

	/**
	 * Updates a single bracket.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item($request) {
		// if id does not match item_id, return error
		// if ($request->get_param('id') != $request->get_param('item_id')) {
		return new WP_Error('cant-update', __('Id passed in url and in request must match', 'text-domain'), array('status' => 400));
		// }

		// // get the update id 
		// $update_id = $request->get_param('item_id');
		// // create an array copy of the request params
		// $bracket_params = $request->get_params();
		// // remove the item_id from the array
		// unset($bracket_params['item_id']);

		// $bracket = Wp_Bracket_Builder_Bracket::from_array($bracket_params);
		// $updated = $this->template_repo->update($bracket);
		// return new WP_REST_Response($updated, 200);
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
	 *  Retrieves a collection of brackets.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_user_items($request) {
		$brackets = $this->template_repo->get_user_brackets();
		return new WP_REST_Response($brackets, 200);
	}

	/**
	 * Activates a single bracket.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function activate_bracket($request) {
		// get id from request
		$id = $request->get_param('id');
		$activated = $this->template_repo->set_active($id, true);
		if ($activated) {
			return new WP_REST_Response(true, 200);
		}
		return new WP_Error('cant-activate', __('message', 'text-domain'), array('status' => 500));
	}

	/**
	 * Deactivates a single bracket.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function deactivate_bracket($request) {
		// get id from request
		$id = $request->get_param('id');
		$deactivated = $this->template_repo->set_active($id, false);
		if ($deactivated) {
			return new WP_REST_Response(false, 200);
		}
		return new WP_Error('cant-deactivate', __('message', 'text-domain'), array('status' => 500));
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
