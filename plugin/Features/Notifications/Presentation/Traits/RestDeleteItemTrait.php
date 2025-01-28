<?php

namespace BMB\Features\Notifications\Presentation\Traits;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Trait for handling DELETE endpoints.
 */
trait RestDeleteItemTrait {
  /**
   * Get configuration for DELETE endpoint.
   */
  protected function get_delete_route_config(): array {
    return [
      'methods' => \WP_REST_Server::DELETABLE,
      'callback' => [$this, 'delete_item'],
      'permission_callback' => [$this, 'delete_item_permissions_check'],
      'args' => array_merge(
        [
          'id' => [
            'required' => true,
            'type' => 'integer',
            'description' => __('Unique identifier for the object.'),
            'minimum' => 1,
          ],
          'force' => [
            'type' => 'boolean',
            'default' => false,
            'description' => __('Whether to bypass trash and force deletion.'),
          ],
        ],
        $this->get_endpoint_args_for_item_schema(\WP_REST_Server::DELETABLE)
      ),
      'schema' => [$this, 'get_delete_item_schema'],
    ];
  }

  /**
   * Check if the current user can delete items.
   * Default implementation requires 'delete_posts' capability.
   */
  public function delete_item_permissions_check($request): bool|WP_Error {
    return current_user_can('delete_posts');
  }

  /**
   * Delete an item.
   */
  public function delete_item($request): WP_REST_Response|WP_Error {
    $id = (int) $request['id'];
    $force = (bool) ($request['force'] ?? false);

    // Get the item before deletion for the response
    $item = $this->get_single_item($id);
    if (is_wp_error($item)) {
      return $item;
    }

    $result = $this->delete_single_item($id, $force);
    if (is_wp_error($result)) {
      return $result;
    }

    $response_data = [
      'deleted' => true,
      'previous' => $this->prepare_item_for_response($item, $request),
    ];

    return rest_ensure_response($response_data);
  }

  /**
   * Get schema for delete response.
   */
  public function get_delete_item_schema(): array {
    return [
      '$schema' => 'http://json-schema.org/draft-04/schema#',
      'title' => $this->rest_base,
      'type' => 'object',
      'properties' => [
        'deleted' => [
          'type' => 'boolean',
          'description' => __('Whether the object was deleted.'),
          'context' => ['view'],
          'readonly' => true,
        ],
        'previous' => [
          'description' => __('The deleted item.'),
          'type' => 'object',
          'context' => ['view'],
          'readonly' => true,
          'properties' => $this->get_public_item_schema()['properties'] ?? [],
        ],
      ],
    ];
  }

  /**
   * Delete a single item.
   * Must be implemented by classes using this trait.
   *
   * @param int  $id    The ID of the item to delete
   * @param bool $force Whether to bypass trash and force deletion
   * @return bool|WP_Error True on success, WP_Error on failure
   */
  abstract protected function delete_single_item(
    int $id,
    bool $force
  ): bool|WP_Error;

  /**
   * Get a single item by ID.
   * Must be implemented by classes using this trait.
   * This is used to get the item before deletion for the response.
   */
  abstract protected function get_single_item(int $id): mixed|WP_Error;
}
