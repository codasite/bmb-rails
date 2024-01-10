<?php
namespace WStrategies\BMB\Includes\Controllers;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WStrategies\BMB\Includes\Hooks\HooksInterface;
use WStrategies\BMB\Includes\Loader;
use WStrategies\BMB\Includes\Service\PaymentProcessors\StripeWebhookService;
use WStrategies\BMB\Includes\Service\StripePaidTournamentService;

class StripePaymentsApi extends WP_REST_Controller implements HooksInterface {
  /**
   * @var string
   */
  protected $namespace;

  /**
   * @var string
   */
  protected $rest_base;
  private StripeWebhookService $webhook_service;
  private StripePaidTournamentService $stripe_paid_tournament_service;

  public function __construct(array $args = []) {
    $this->namespace = 'wp-bracket-builder/v1';
    $this->rest_base = 'stripe';
    $this->stripe_paid_tournament_service =
      $args['stripe_paid_tournament_service'] ??
      new StripePaidTournamentService();
    $this->webhook_service =
      $args['webhook_service'] ?? new StripeWebhookService();
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
        'args' => $this->get_endpoint_args_for_item_schema(),
      ],
      'schema' => [$this, 'get_public_item_schema'],
    ]);
    register_rest_route($namespace, '/' . $base . '/payment-intent', [
      [
        'methods' => WP_REST_Server::READABLE,
        'callback' => [$this, 'get_payment_intent'],
        'permission_callback' => [$this, 'customer_permission_check'],
      ],
      'schema' => [$this, 'get_public_item_schema'],
    ]);
  }

  /**
   * @param WP_REST_Request<array{}> $request
   */
  public function handle_webhook(WP_REST_Request $request): WP_REST_Response {
    $body = $request->get_body();
    if (!$body) {
      return new WP_REST_Response('body is required', 400);
    }
    try {
      $this->webhook_service->process_webhook(
        $body,
        $request->get_header('Stripe-Signature')
      );
    } catch (\Stripe\Exception\SignatureVerificationException $e) {
      return new WP_REST_Response($e->getMessage(), 400);
    }
    return new WP_REST_Response('webhook success', 200);
  }

  /**
   * @param WP_REST_Request<array{play_id: int}> $request
   */
  public function get_payment_intent(
    WP_REST_Request $request
  ): WP_REST_Response {
    if (!isset($request['play_id'])) {
      return new WP_REST_Response('play_id is required', 400);
    }
    try {
      $play_id = $request['play_id'];
      $payment_intent = $this->stripe_paid_tournament_service->get_play_payment_intent(
        $play_id
      );
      if (!$payment_intent) {
        return new WP_REST_Response('payment intent not found', 404);
      }
      return new WP_REST_Response(
        ['client_secret' => $payment_intent->client_secret],
        200
      );
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
