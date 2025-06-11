<?php
namespace WStrategies\BMB\Includes\Controllers;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WStrategies\BMB\Includes\Hooks\HooksInterface;
use WStrategies\BMB\Includes\Hooks\Loader;

/**
 * Base controller for HTML fragment endpoints.
 * Provides common functionality for paginated HTML responses.
 */
abstract class HtmlFragmentApiBase extends WP_REST_Controller implements
  HooksInterface {
  protected $namespace = 'wp-bracket-builder/v1';
  protected $rest_base;

  public function load(Loader $loader): void {
    $loader->add_action('rest_api_init', [$this, 'register_routes']);
  }

  /**
   * Register the routes for this controller.
   */
  public function register_routes(): void {
    register_rest_route($this->namespace, '/' . $this->rest_base, [
      [
        'methods' => WP_REST_Server::READABLE,
        'callback' => [$this, 'get_items'],
        'permission_callback' => [$this, 'get_items_permissions_check'],
        'args' => $this->get_collection_params(),
      ],
    ]);
  }

  /**
   * Get collection parameters for pagination and filtering.
   */
  public function get_collection_params(): array {
    return [
      'page' => [
        'description' => 'Current page of the collection.',
        'type' => 'integer',
        'default' => 1,
        'sanitize_callback' => 'absint',
      ],
      'per_page' => [
        'description' =>
          'Maximum number of items to be returned in result set.',
        'type' => 'integer',
        'default' => 10,
        'sanitize_callback' => 'absint',
      ],
      'status' => [
        'description' => 'Filter by bracket status.',
        'type' => 'string',
        'default' => 'live',
        'sanitize_callback' => 'sanitize_text_field',
      ],
    ];
  }

  /**
   * Check permissions for getting items.
   */
  public function get_items_permissions_check($request): bool {
    // Public endpoints - no authentication required
    return true;
  }

  /**
   * Format HTML response with pagination metadata.
   */
  protected function format_html_response(
    string $html,
    array $pagination
  ): WP_REST_Response {
    $response_data = [
      'html' => $html,
      'pagination' => $pagination,
    ];

    $response = new WP_REST_Response($response_data, 200);

    // Set content type header
    $response->header('Content-Type', 'application/json');

    return $response;
  }

  /**
   * Get items - can be overridden by child classes to provide specific implementation.
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return WP_REST_Response|WP_Error Response object or WP_Error object if something went wrong.
   */
  public function get_items($request) {
    return new WP_Error(
      'invalid-method',
      sprintf(
        'Method "%s" not implemented. Must be overridden in subclass.',
        __METHOD__
      ),
      ['status' => 405]
    );
  }
}
