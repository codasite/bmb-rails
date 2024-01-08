<?php
namespace WStrategies\BMB\Includes\Controllers;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WStrategies\BMB\Includes\Hooks\HooksInterface;
use WStrategies\BMB\Includes\Loader;

class StripePaymentsApi extends WP_REST_Controller implements HooksInterface {
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
  public function __construct($args = []) {
    $this->namespace = 'wp-bracket-builder/v1';
    $this->rest_base = 'stripe';
  }

  public function load(Loader $loader): void {
    $loader->add_action('rest_api_init', [$this, 'register_routes']);
  }

  public function register_routes(): void {
    $namespace = $this->namespace;
    $base = $this->rest_base;
    register_rest_route($namespace, '/webhook' . $base, [
      [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => [$this, 'handle_webhook'],
        'permission_callback' => [$this, 'webhook_verification'],
        'args' => $this->get_endpoint_args_for_item_schema(
          WP_REST_Server::CREATABLE
        ),
      ],
      'schema' => [$this, 'get_public_item_schema'],
    ]);
    register_rest_route($namespace, '/create-payment-intent' . $base, [
      [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => [$this, 'create_payment_intent'],
        'permission_callback' => [$this, 'customer_permission_check'],
        'args' => $this->get_endpoint_args_for_item_schema(
          WP_REST_Server::CREATABLE
        ),
      ],
      'schema' => [$this, 'get_public_item_schema'],
    ]);
  }

  public function handle_webhook(WP_REST_Request $request): WP_REST_Response {
    $body = $request->get_body();
    $body = json_decode($body, true);
    return new WP_REST_Response('hello from webhook', 200);
  }

  public function create_payment_intent(
    WP_REST_Request $request
  ): WP_REST_Response {
    $body = $request->get_body();
    $body = json_decode($body, true);
    return new WP_REST_Response('hello from webhook', 200);
  }

  /**
   * Verify that the request is coming from Stripe
   *
   * @param WP_REST_Request $request Full details about the request.
   * @return bool|WP_Error
   */
  public function webhook_verification(
    WP_REST_Request $request
  ): bool|WP_Error {
    return true;
    // return current_user_can('read');
  }

  /**
   * Check if a given request has customer access to this plugin. Anyone can view the data.
   *
   * @param WP_REST_Request $request Full details about the request.
   *
   * @return WP_Error|bool
   */
  public function customer_permission_check(
    WP_REST_Request $request
  ): WP_Error|bool {
    return true;
    // return current_user_can('read');
  }
}
