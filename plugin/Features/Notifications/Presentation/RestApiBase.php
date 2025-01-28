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
  protected $notification_service;
  protected $namespace = 'bmb/v1';
  protected $rest_base;
  protected $schema;
  protected ApiSerializerInterface $serializer;

  public function __construct() {
    if (empty($this->rest_base)) {
      throw new \RuntimeException('REST base must be defined in child class');
    }
    if (empty($this->serializer)) {
      throw new \RuntimeException('Serializer must be defined in child class');
    }
  }

  public function load(Loader $loader): void {
    $loader->add_action('rest_api_init', [$this, 'register_routes']);
  }

  public function register_routes(): void {
    register_rest_route($this->namespace, '/' . $this->rest_base, [
      [
        'methods' => \WP_REST_Server::READABLE,
        'callback' => [$this, 'get_items'],
        'permission_callback' => [$this, 'get_items_permissions_check'],
        'args' => $this->get_collection_params(),
        'schema' => [$this, 'get_public_item_schema'],
      ],
      [
        'methods' => \WP_REST_Server::CREATABLE,
        'callback' => [$this, 'create_item'],
        'permission_callback' => [$this, 'create_item_permissions_check'],
        'args' => $this->get_endpoint_args(\WP_REST_Server::CREATABLE),
        'schema' => [$this, 'get_public_item_schema'],
      ],
    ]);

    register_rest_route(
      $this->namespace,
      '/' . $this->rest_base . '/(?P<id>[\d]+)',
      [
        [
          'methods' => \WP_REST_Server::READABLE,
          'callback' => [$this, 'get_item'],
          'permission_callback' => [$this, 'get_item_permissions_check'],
          'args' => $this->get_endpoint_args(\WP_REST_Server::READABLE),
          'schema' => [$this, 'get_public_item_schema'],
        ],
        [
          'methods' => \WP_REST_Server::EDITABLE,
          'callback' => [$this, 'update_item'],
          'permission_callback' => [$this, 'update_item_permissions_check'],
          'args' => $this->get_endpoint_args(\WP_REST_Server::EDITABLE),
          'schema' => [$this, 'get_public_item_schema'],
        ],
        [
          'methods' => \WP_REST_Server::DELETABLE,
          'callback' => [$this, 'delete_item'],
          'permission_callback' => [$this, 'delete_item_permissions_check'],
          'args' => $this->get_endpoint_args(\WP_REST_Server::DELETABLE),
          'schema' => [$this, 'get_delete_item_schema'],
        ],
      ]
    );
  }

  // Default permission callbacks
  public function get_items_permissions_check($request): bool|WP_Error {
    return current_user_can('read');
  }

  public function get_item_permissions_check($request): bool|WP_Error {
    return current_user_can('read');
  }

  public function create_item_permissions_check($request): bool|WP_Error {
    return current_user_can('edit_posts');
  }

  public function update_item_permissions_check($request): bool|WP_Error {
    return current_user_can('edit_posts');
  }

  public function delete_item_permissions_check($request): bool|WP_Error {
    return current_user_can('delete_posts');
  }

  // Default route callbacks that throw "not implemented" errors
  public function get_items($request): WP_REST_Response|WP_Error {
    return new WP_Error('rest_method_not_allowed', __('Method not allowed.'), [
      'status' => 405,
    ]);
  }

  public function get_item($request): WP_REST_Response|WP_Error {
    return new WP_Error('rest_method_not_allowed', __('Method not allowed.'), [
      'status' => 405,
    ]);
  }

  public function create_item($request): WP_REST_Response|WP_Error {
    return new WP_Error('rest_method_not_allowed', __('Method not allowed.'), [
      'status' => 405,
    ]);
  }

  public function update_item($request): WP_REST_Response|WP_Error {
    return new WP_Error('rest_method_not_allowed', __('Method not allowed.'), [
      'status' => 405,
    ]);
  }

  public function delete_item($request): WP_REST_Response|WP_Error {
    return new WP_Error('rest_method_not_allowed', __('Method not allowed.'), [
      'status' => 405,
    ]);
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

  /**
   * Get the publicly visible schema for the endpoint.
   */
  public function get_public_item_schema(): array {
    $schema = $this->get_item_schema();

    if (empty($schema['properties'])) {
      return [];
    }

    return $schema;
  }

  /**
   * Get schema for delete endpoint.
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
      ],
    ];
  }
}
