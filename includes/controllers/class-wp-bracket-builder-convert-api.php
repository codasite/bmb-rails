<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'service/class-wp-bracket-builder-aws-service.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-config.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-bracket-config-repo.php';

class Wp_Bracket_Builder_Convert_Api extends WP_REST_Controller {
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
		$this->namespace = 'wp-bracket-builder/v1';
		$this->rest_base = 'html-to-image';
	}

	/**
	 * Register the routes for bracket objects.
	 * Adapted from: https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
	 */
	public function register_routes() {
		$namespace = $this->namespace;
		$base = $this->rest_base;
		register_rest_route($namespace, '/' . $base, array(
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
	 * Converts html to image.
	 * 
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */

	public function html_to_image($request) {
		// get the entire request body
		$body = json_decode($request->get_body(), true);
		$theme_mode = $body['themeMode'] ?? null;
		unset($body['themeMode']);

		$lambda_service = new Wp_Bracket_Builder_Lambda_Service();
		$res = $lambda_service->html_to_image($body);

		if (!is_wp_error($res) && isset($res->imageUrl)) {
			// build a config object
			$config = new Wp_Bracket_Builder_Bracket_Config($body['html'], $theme_mode, $res->imageUrl);
			// Add the image url to the user's session
			$config_repo = new Wp_Bracket_Builder_Bracket_Config_Repository();
			$config_repo->add($config, $theme_mode);
			return new WP_REST_Response($res, 200);
		} else if (is_wp_error($res)) {
			return $res;
		} else {
			return new WP_Error('error', __('Error converting HTML to image. Image url not found', 'text-domain'), array('status' => 500));
		}
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
