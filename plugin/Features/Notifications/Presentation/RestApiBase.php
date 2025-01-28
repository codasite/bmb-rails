<?php

namespace BMB\Features\Notifications\Presentation;

use BMB\Features\Notifications\Domain\NotificationService;
use WStrategies\BMB\Includes\Hooks\HooksInterface;
use WStrategies\BMB\Includes\Hooks\Loader;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WStrategies\BMB\Features\Notifications\Infrastructure\DomainRepositoryInterface;
use WStrategies\BMB\Includes\Service\Serializer\ApiSerializerInterface;

abstract class RestApiBase extends WP_REST_Controller implements
  HooksInterface {
  protected $namespace = 'bmb/v1';
  protected $rest_base;
  protected $schema;
  protected ApiSerializerInterface $serializer;
  protected DomainRepositoryInterface $repository;

  public function __construct(array $args = []) {
    $this->rest_base = $args['rest_base'];
    $this->serializer = $args['serializer'];
    $this->repository = $args['repository'];
  }

  public function load(Loader $loader): void {
    $loader->add_action('rest_api_init', [$this, 'register_routes']);
  }

  public function register_routes(): void {
    $root_routes = $this->get_root_routes();
    if (!empty($root_routes)) {
      register_rest_route(
        $this->namespace,
        '/' . $this->rest_base,
        $root_routes
      );
    }

    $single_item_routes = $this->get_single_item_routes();
    if (!empty($single_item_routes)) {
      register_rest_route(
        $this->namespace,
        '/' . $this->rest_base . '/(?P<id>[\d]+)',
        $single_item_routes
      );
    }
  }

  /**
   * Get routes for the root endpoint (e.g. /items).
   */
  protected function get_root_routes(): array {
    $routes = [];

    $collection_config = $this->get_collection_route_config();
    if (!empty($collection_config)) {
      $routes[] = $collection_config;
    }

    $create_config = $this->get_create_route_config();
    if (!empty($create_config)) {
      $routes[] = $create_config;
    }

    return $routes;
  }

  /**
   * Get routes for single item endpoints (e.g. /items/{id}).
   */
  protected function get_single_item_routes(): array {
    $routes = [];

    $get_config = $this->get_single_item_route_config();
    if (!empty($get_config)) {
      $routes[] = $get_config;
    }

    $update_config = $this->get_update_route_config();
    if (!empty($update_config)) {
      $routes[] = $update_config;
    }

    $delete_config = $this->get_delete_route_config();
    if (!empty($delete_config)) {
      $routes[] = $delete_config;
    }

    return $routes;
  }

  /**
   * Get configuration for GET collection endpoint.
   * Override this in traits or child classes to enable the endpoint.
   */
  protected function get_collection_route_config(): array {
    return [];
  }

  /**
   * Get configuration for POST endpoint.
   * Override this in traits or child classes to enable the endpoint.
   */
  protected function get_create_route_config(): array {
    return [];
  }

  /**
   * Get configuration for GET single item endpoint.
   * Override this in traits or child classes to enable the endpoint.
   */
  protected function get_single_item_route_config(): array {
    return [];
  }

  /**
   * Get configuration for PUT/PATCH endpoint.
   * Override this in traits or child classes to enable the endpoint.
   */
  protected function get_update_route_config(): array {
    return [];
  }

  /**
   * Get configuration for DELETE endpoint.
   * Override this in traits or child classes to enable the endpoint.
   */
  protected function get_delete_route_config(): array {
    return [];
  }

  public function get_collection_params() {
    return parent::get_collection_params();
  }

  public function get_endpoint_args(
    string $method = \WP_REST_Server::CREATABLE
  ) {
    return $this->get_endpoint_args_for_item_schema($method);
  }

  public function get_item_schema(): array {
    if ($this->schema) {
      return $this->schema;
    }

    $schema = [
      '$schema' => 'http://json-schema.org/draft-04/schema#',
      'title' => $this->rest_base,
      'type' => 'object',
      'properties' => $this->serializer->get_schema_properties(),
    ];

    $this->schema = $schema;
    return $schema;
  }

  public function get_public_item_schema(): array {
    $schema = $this->get_item_schema();

    if (empty($schema['properties'])) {
      return [];
    }

    return $schema;
  }

  /**
   * Prepare the item for the REST response.
   * Default implementation uses the serializer.
   *
   * @param mixed           $item    WordPress representation of the item.
   * @param WP_REST_Request $request Request object.
   * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
   */
  public function prepare_item_for_response(
    $item,
    $request
  ): WP_REST_Response|WP_Error {
    $data = $this->serializer->serialize($item);
    return rest_ensure_response($data);
  }
}
