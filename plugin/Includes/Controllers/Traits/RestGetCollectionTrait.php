<?php

namespace WStrategies\BMB\Includes\Controllers\Traits;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Trait for handling GET collection endpoints.
 */
trait RestGetCollectionTrait {
  /**
   * Get configuration for GET collection endpoint.
   */
  protected function get_collection_route_config(): array {
    return [
      'methods' => \WP_REST_Server::READABLE,
      'callback' => [$this, 'get_items'],
      'permission_callback' => [$this, 'get_items_permissions_check'],
      'args' => $this->get_collection_params(),
      'schema' => [$this, 'get_public_item_schema'],
    ];
  }

  /**
   * Check if the current user can view items.
   * Default implementation requires 'read' capability.
   */
  public function get_items_permissions_check($request): bool|WP_Error {
    return current_user_can('read');
  }

  /**
   * Get a collection of items.
   */
  public function get_items($request): WP_REST_Response|WP_Error {
    $page = $request['page'] ?? 1;
    $per_page = $request['per_page'] ?? 10;
    $search = $request['search'] ?? '';

    $items = $this->get_collection_items($page, $per_page, $search);
    if (is_wp_error($items)) {
      return $items;
    }

    $data = [];
    foreach ($items as $item) {
      $data[] = $this->prepare_item_for_response($item, $request);
    }

    return rest_ensure_response($data);
  }

  /**
   * Get items for the collection.
   * Must be implemented by classes using this trait.
   */
  abstract protected function get_collection_items(
    int $page,
    int $per_page,
    string $search
  ): array|WP_Error;
}
