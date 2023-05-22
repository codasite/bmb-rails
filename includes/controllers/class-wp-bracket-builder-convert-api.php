<?php
require_once plugin_dir_path(dirname(__FILE__)) . 'service/class-wp-bracket-builder-aws-service.php';
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
		$body = $request->get_body();
		// $convert_url = 'http://localhost:8080/convert';
		// // Make a request to the convert url using POST, content type application/json, and the html as the body, and accept *

		// $res = wp_remote_post($convert_url, array(
		// 	'headers' => array(
		// 		'Content-Type' => 'application/json',
		// 		'Accept' => '*',
		// 	),
		// 	'body' => $body,
		// ));

		// if (is_wp_error($res) || wp_remote_retrieve_response_code($res) !== 200) {
		// 	return new WP_Error('error', __('There was an error converting the html to an image', 'text-domain'), array('status' => 500));
		// }

		// // get the response body as json
		// $res_body = json_decode(wp_remote_retrieve_body($res));

		$lambda_service = new LambdaServicex();
		$lambda_service . invoke('convert', $body);

		$res_body = 'hi';

		return new WP_REST_Response($res_body, 200);
		// return new WP_REST_Response('hi', 200);
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
