<?php
namespace WStrategies\BMB\Includes\Controllers;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WStrategies\BMB\Includes\Hooks\HooksInterface;
use WStrategies\BMB\Includes\Loader;
use WStrategies\BMB\Includes\Service\PaymentProcessors\StripePayments;

class StripePaymentsApi extends WP_REST_Controller implements HooksInterface {
  /**
   * @var string
   */
  protected $namespace;

  /**
   * @var string
   */
  protected $rest_base;
  private StripePayments $stripe_payments;

  /**
   * @param array{stripe_payments?: StripePayments} $args
   */
  public function __construct(array $args = []) {
    $this->namespace = 'wp-bracket-builder/v1';
    $this->rest_base = 'stripe';
    try {
      $this->stripe_payments = $args['stripe_payments'] ?? new StripePayments();
    } catch (\Exception $e) {
      error_log('Caught error: ' . $e->getMessage());
    }
  }

  public function load(Loader $loader): void {
    $loader->add_action('rest_api_init', [$this, 'register_routes']);
  }

  public function register_routes(): void {
    $namespace = $this->namespace;
    $base = $this->rest_base;
    register_rest_route($namespace, '/' . $base . '/webhook', [
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
    register_rest_route($namespace, '/' . $base . '/create-payment-intent', [
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

  /**
   * @param WP_REST_Request<array{}> $request
   */
  public function handle_webhook(WP_REST_Request $request): WP_REST_Response {
    $body = $request->get_body();
    $body = json_decode($body, true);
    if (!$body) {
      return new WP_REST_Response('body is required', 400);
    }
    $this->stripe_payments->process_webhook($body);
    return new WP_REST_Response('webhook success', 200);
  }

  /**
   * @param WP_REST_Request<array{bracket_id: int}> $request
   */
  public function create_payment_intent(
    WP_REST_Request $request
  ): WP_REST_Response {
    if (!isset($request['bracket_id'])) {
      return new WP_REST_Response('bracket_id is required', 400);
    }
    try {
      $bracket_id = $request['bracket_id'];
      $client_secret = $this->stripe_payments->create_payment_intent_for_paid_bracket(
        $bracket_id
      );
      return new WP_REST_Response(['client_secret' => $client_secret], 200);
    } catch (\Exception $e) {
      return new WP_REST_Response($e->getMessage(), 500);
    }
  }

  /**
   * Verify that the request is coming from Stripe
   *
   * @param WP_REST_Request<array{}> $request Full details about the request.
   * @return bool|WP_Error
   */
  public function webhook_verification(
    WP_REST_Request $request
  ): bool|WP_Error {
    return true;
  }

  /**
   * Check if a given request has customer access to this plugin. Anyone can view the data.
   *
   * @param WP_REST_Request<array{}> $request Full details about the request.
   *
   * @return WP_Error|bool
   */
  public function customer_permission_check(
    WP_REST_Request $request
  ): WP_Error|bool {
    return true;
  }
}
