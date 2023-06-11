<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'service/class-wp-bracket-builder-aws-service.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'domain/class-wp-bracket-builder-bracket-config.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'repository/class-wp-bracket-builder-bracket-config-repo.php';
require_once plugin_dir_path(dirname(__FILE__)) . 'class-wp-bracket-builder-utils.php';


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
		$utils = new Wp_Bracket_Builder_Utils();
		// get the entire request body
		$body = json_decode($request->get_body(), true);
		$theme_mode = $body['themeMode'] ?? null;
		// $utils->log_sentry_message('themeMode: ' . $theme_mode . ' key: ' . $body['key'], \Sentry\Severity::info()); // TODO: Remove this line

		if (!$theme_mode) {
			$utils->log_sentry_error('Theme mode is required. Request: ' . json_encode($body));
			return new WP_Error('error', __('Theme mode is required', 'text-domain'), array('status' => 400));
		}

		$lambda_service = new Wp_Bracket_Builder_Lambda_Service();
		$res = $lambda_service->html_to_image($body);

		if (!is_wp_error($res) && isset($res['imageUrl'])) {
			// $utils->log_sentry_message('themeMode: ' . $theme_mode . ' url: ' . $res['imageUrl'] . ' key: ' . $body['key'], \Sentry\Severity::info()); // TODO: Remove this line
			// build a config object
			$config = new Wp_Bracket_Builder_Bracket_Config($body['html'], $theme_mode, $res['imageUrl']);
			// Add the image url to the user's session
			$config_repo = new Wp_Bracket_Builder_Bracket_Config_Repository();
			$config_repo->add($config, $theme_mode);

			return new WP_REST_Response($res, 200);
		} else {
			$error = $res instanceof WP_Error ? $res : new WP_Error('error', __('Error converting HTML to image. Image url not found. Response: ' . json_encode($res), 'text-domain'), array('status' => 500));
			$utils->log_sentry_message('Error converting HTML to image. Image url not found. Response: ' . json_encode($res), \Sentry\Severity::error());
			return $error;
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
