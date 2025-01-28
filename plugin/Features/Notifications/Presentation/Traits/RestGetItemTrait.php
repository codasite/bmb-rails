<?php

namespace BMB\Features\Notifications\Presentation\Traits;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Trait for handling GET single item endpoints
 */
trait RestGetItemTrait {
  public function get_item_permissions_check($request): bool|WP_Error {
    return current_user_can('read');
  }

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
