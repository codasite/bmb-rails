<?php

namespace WStrategies\BMB\Includes\Controllers\Traits;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Trait for handling POST endpoints
 */
trait RestCreateItemTrait {
  public function create_item_permissions_check($request): bool|WP_Error {
    return current_user_can('edit_posts');
  }

  public function create_item($request): WP_REST_Response|WP_Error {
    $prepared = $this->prepare_item_for_database($request);
    if (is_wp_error($prepared)) {
      return $prepared;
    }

    $item = $this->create_single_item($prepared);
    if (is_wp_error($item)) {
      return $item;
    }

    $response = $this->prepare_item_for_response($item, $request);
    return rest_ensure_response($response);
  }

  /**
   * Create a single item.
   * Must be implemented by classes using this trait.
   */
  abstract protected function create_single_item(array $prepared): mixed;
}
