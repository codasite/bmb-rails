<?php

namespace BMB\Features\Notifications\Presentation;

use BMB\Features\Notifications\Domain\NotificationService;
use WStrategies\BMB\Includes\Hooks\HooksInterface;
use WStrategies\BMB\Includes\Hooks\Loader;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WStrategies\BMB\Includes\Service\Serializer\ApiSerializerInterface;

abstract class RestApiBase extends WP_REST_Controller implements
  HooksInterface {
  protected $namespace = 'bmb/v1';
  protected $rest_base;
  protected $schema;
  protected ApiSerializerInterface $serializer;

  public function __construct(
    string $rest_base,
    ApiSerializerInterface $serializer
  ) {
    $this->rest_base = $rest_base;
    $this->serializer = $serializer;
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

    if (method_exists($this, 'get_items')) {
      $routes[] = [
        'methods' => \WP_REST_Server::READABLE,
        'callback' => [$this, 'get_items'],
        'permission_callback' => [$this, 'get_items_permissions_check'],
        'args' => $this->get_collection_params(),
        'schema' => [$this, 'get_public_item_schema'],
      ];
    }

    if (method_exists($this, 'create_item')) {
      $routes[] = [
        'methods' => \WP_REST_Server::CREATABLE,
        'callback' => [$this, 'create_item'],
        'permission_callback' => [$this, 'create_item_permissions_check'],
        'args' => $this->get_endpoint_args(\WP_REST_Server::CREATABLE),
        'schema' => [$this, 'get_public_item_schema'],
      ];
    }

    return $routes;
  }

  /**
   * Get routes for single item endpoints (e.g. /items/{id}).
   */
  protected function get_single_item_routes(): array {
    $routes = [];

    if (method_exists($this, 'get_item')) {
      $routes[] = [
        'methods' => \WP_REST_Server::READABLE,
        'callback' => [$this, 'get_item'],
        'permission_callback' => [$this, 'get_item_permissions_check'],
        'args' => $this->get_endpoint_args(\WP_REST_Server::READABLE),
        'schema' => [$this, 'get_public_item_schema'],
      ];
    }

    if (method_exists($this, 'update_item')) {
      $routes[] = [
        'methods' => \WP_REST_Server::EDITABLE,
        'callback' => [$this, 'update_item'],
        'permission_callback' => [$this, 'update_item_permissions_check'],
        'args' => $this->get_endpoint_args(\WP_REST_Server::EDITABLE),
        'schema' => [$this, 'get_public_item_schema'],
      ];
    }

    if (method_exists($this, 'delete_item')) {
      $routes[] = [
        'methods' => \WP_REST_Server::DELETABLE,
        'callback' => [$this, 'delete_item'],
        'permission_callback' => [$this, 'delete_item_permissions_check'],
        'args' => $this->get_endpoint_args(\WP_REST_Server::DELETABLE),
        'schema' => [$this, 'get_delete_item_schema'],
      ];
    }

    return $routes;
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
      ],
    ];
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
