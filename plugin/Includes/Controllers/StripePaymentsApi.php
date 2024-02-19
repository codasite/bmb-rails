<?php
namespace WStrategies\BMB\Includes\Controllers;

use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WStrategies\BMB\Includes\Hooks\HooksInterface;
use WStrategies\BMB\Includes\Hooks\Loader;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Service\PaidTournamentService\StripeConnectedAccount;
use WStrategies\BMB\Includes\Service\PaidTournamentService\StripePaidTournamentService;
use WStrategies\BMB\Includes\Service\PaymentProcessors\StripeWebhookService;
use WStrategies\BMB\Includes\Service\Stripe\StripeClientFactory;

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
  private StripePaidTournamentService $tournament_service;
  private PlayRepo $play_repo;
  private StripeClient $stripe;
  private StripeConnectedAccount $connected_account;

  /**
   * @param array<string, mixed> $args
   */
  public function __construct(array $args = []) {
    $this->namespace = 'wp-bracket-builder/v1';
    $this->rest_base = 'stripe';
    $this->webhook_service =
      $args['webhook_service'] ?? new StripeWebhookService();
    $this->tournament_service =
      $args['tournament_service'] ?? new StripePaidTournamentService();
    $this->play_repo = $args['play_repo'] ?? new PlayRepo();
    $this->stripe = $args['stripe_client'] = (new StripeClientFactory())->createStripeClient();
    $this->connected_account =
      $args['connected_account'] ??
      new StripeConnectedAccount([
        'stripe_client' => $this->stripe,
      ]);
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
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => [$this, 'create_payment_intent'],
        'permission_callback' => [$this, 'author_permission_check'],
        'args' => $this->get_endpoint_args_for_item_schema(
          WP_REST_Server::CREATABLE
        ),
      ],
      'schema' => [$this, 'get_public_item_schema'],
    ]);
    register_rest_route($namespace, '/' . $base . '/onboarding-link', [
      [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => [$this, 'onboarding_link'],
        'permission_callback' => [$this, 'author_permission_check'],
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
    if (!$body) {
      return new WP_REST_Response('body is required', 400);
    }
    try {
      $this->webhook_service->process_webhook(
        $body,
        $request->get_header('Stripe-Signature')
      );
    } catch (\Exception $e) {
      return new WP_REST_Response($e->getMessage(), 400);
    }

    return new WP_REST_Response('webhook success', 200);
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
   * @param WP_REST_Request<array{play_id: int}> $request
   */
  public function create_payment_intent(
    WP_REST_Request $request
  ): WP_REST_Response {
    if (!isset($request['play_id'])) {
      return new WP_REST_Response('play_id is required', 400);
    }
    try {
      $play_id = $request['play_id'];
      $play = $this->play_repo->get($play_id);
      if (!$play) {
        return new WP_REST_Response('play not found', 404);
      }
      $existing_payment_intent_id = $this->tournament_service->get_play_payment_intent_id(
        $play_id
      );
      $payment_intent = null;
      if ($existing_payment_intent_id) {
        $payment_intent = $this->stripe->paymentIntents->retrieve(
          $existing_payment_intent_id
        );
      }
      $payment_intent =
        $payment_intent ??
        $this->tournament_service->create_payment_intent_for_paid_tournament_play(
          $play
        );
      return new WP_REST_Response(
        [
          'client_secret' => $payment_intent->client_secret,
          'amount' => $payment_intent->amount,
        ],
        200
      );
    } catch (\Exception $e) {
      return new WP_REST_Response($e->getMessage(), 500);
    }
  }

  /**
   * @param WP_REST_Request<array> $request
   *
   * @return WP_REST_Response
   * @throws ApiErrorException
   */
  public function onboarding_link(
    WP_REST_Request $request
  ): WP_REST_Response {
    return new WP_REST_Response(
      [
        'url' => $this->connected_account->get_onboarding_link(),
      ],
      200);
  }

  /**
   * Check if a given request has customer access to this plugin. Anyone can view the data.
   *
   * @param WP_REST_Request<array{}> $request Full details about the request.
   *
   * @return WP_Error|bool
   */
  public function author_permission_check(
    WP_REST_Request $request
  ): WP_Error|bool {
    return current_user_can('wpbb_create_payment_intent', $request['play_id']);
  }
}
