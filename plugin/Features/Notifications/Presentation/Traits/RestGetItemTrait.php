<?php

namespace BMB\Features\Notifications\Presentation\Traits;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Trait for handling GET single item endpoints.
 */
trait RestGetItemTrait {
  /**
   * Get configuration for GET single item endpoint.
   */
  protected function get_single_item_route_config(): array {
    return [
      'methods' => \WP_REST_Server::READABLE,
      'callback' => [$this, 'get_item'],
      'permission_callback' => [$this, 'get_item_permissions_check'],
      'args' => array_merge(
        [
          'id' => [
            'required' => true,
            'type' => 'integer',
            'description' => __('Unique identifier for the object.'),
            'minimum' => 1,
          ],
        ],
        $this->get_endpoint_args_for_item_schema(\WP_REST_Server::READABLE)
      ),
      'schema' => [$this, 'get_public_item_schema'],
    ];
  }

  /**
   * Check if the current user can view a specific item.
   * Default implementation requires 'read' capability.
   */
  public function get_item_permissions_check($request): bool|WP_Error {
    return current_user_can('read');
  }

  /**
   * Get a single item.
   */
  public function get_item($request): WP_REST_Response|WP_Error {
    $id = (int) $request['id'];
    $item = $this->get_single_item($id);

    if (is_wp_error($item)) {
      return $item;
    }

    return rest_ensure_response(
      $this->prepare_item_for_response($item, $request)
    );
  }

  /**
   * Get a single item by ID.
   * Must be implemented by classes using this trait.
   */
  abstract protected function get_single_item(int $id): mixed|WP_Error;
}
