<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wpbb-bracket-tournament-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wpbb-bracket-tournament.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'service/class-wpbb-score-service.php';
// require_once plugin_dir_path(dirname(__FILE__)) . 'validations/class-wp-bracket-builder-bracket-api-validation.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'service/class-wpbb-mailchimp-email-service.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'service/class-wpbb-notification-service.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'service/class-wpbb-notification-service-interface.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'service/class-wpbb-email-service-interface.php';


class Wpbb_BracketTournamentApi extends WP_REST_Controller
{

	/**
	 * @var Wpbb_BracketTournamentRepo
	 */
	private $tournament_repo;

	/**
	 * @var string
	 */
	protected $namespace;

	/**
	 * @var string
	 */
	protected $rest_base;

	/**
	 * @var Wpbb_Score_Service
	 */
	private $score_service;

	/**
	 * @var Wpbb_Email_Service_Interface
	 */
	private ?Wpbb_Email_Service_Interface $email_service;

	/**
	 * @var Wpbb_Notification_Service_Interface
	 */
	private ?Wpbb_Notification_Service_Interface $notification_service;

	public function __construct($args = array()) {
		$this->tournament_repo = $args['tournament_repo'] ?? new Wpbb_BracketTournamentRepo();
		$this->score_service = $args['score_service'] ?? new Wpbb_Score_Service();
		$this->namespace = 'wp-bracket-builder/v1';
		$this->rest_base = 'tournaments';
		try {
			// $this->email_service = $args['email_service'] ?? new Wpbb_Mailchimp_Email_Service();
			$this->notification_service = $args['notification_service'] ?? new Wpbb_Notification_Service();
		} catch (Exception $e) {
			$this->email_service = null;
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
	}

	/**
	 * Retrieves a collection of tournaments.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items($request) {
		$tournaments = $this->tournament_repo->get_all();
		return new WP_REST_Response($tournaments, 200);
	}

	/**
	 * Retrieves a single tournament.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item($request) {
		// get id from request
		$id = $request->get_param('item_id');
		$tournament = $this->tournament_repo->get($id);
		return new WP_REST_Response($tournament, 200);
	}

	/**
	 * Creates a single tournament.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item($request) {
		$params = $request->get_params();

		if (!isset($params['author'])) {
			$params['author'] = get_current_user_id();
		}
		if (isset($params['bracket_template']) && !isset($params['bracket_template']['author'])) {
			$params['bracket_template']['author'] = get_current_user_id();
		}

		try {
			$tournament = Wp_Bracket_Builder_Bracket_Tournament::from_array($params);
		} catch (Wpbb_ValidationException $e) {
			return new WP_Error('validation-error', $e->getMessage(), array('status' => 400));
		}

		$saved = $this->tournament_repo->add($tournament);
		return new WP_REST_Response($saved, 201);
	}

	/**
	 * Updates a single bracket.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item($request) {
		$tournament_id = $request->get_param('item_id');
		$data = $request->get_params();
		$updated = $this->tournament_repo->update($tournament_id, $data);
		$this->score_service->score_tournament_plays($updated);

		$notify = $request->get_param('update_notify_participants');
		if ($this->notification_service && $notify) {
			$this->notification_service->notify_tournament_results_updated($tournament_id);
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
		$deleted = $this->tournament_repo->delete($id);
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
