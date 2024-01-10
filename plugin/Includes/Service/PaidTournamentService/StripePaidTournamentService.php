<?php
namespace WStrategies\BMB\Includes\Service\PaidTournamentService;

use Stripe\Exception\InvalidArgumentException;
use Stripe\PaymentIntent;
use Stripe\StripeClient;
use WStrategies\BMB\Includes\Controllers\ApiListeners\BracketPlayCreateListenerBase;
use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductUtils;

class StripePaidTournamentService extends BracketPlayCreateListenerBase {
  public static string $PAYMENT_INTENT_ID_META_KEY = 'payment_intent_id';
  public static string $SHOULD_CREATE_STRIPE_PAYMENT_INTENT_REQUEST_DATA_KEY = 'create_stripe_payment_intent';
  public static string $CLIENT_SECRET_RESPONSE_DATA_KEY = 'stripe_payment_intent_client_secret';
  public static string $INTENT_ID_KEY = 'stripe_payment_intent_id';

  private StripeClient $stripe;
  private BracketProductUtils $bracket_product_utils;
  // This is set after the payment intent is created for a paid play,
  // and then the client secret appended to the play response data
  private PaymentIntent $stripe_payment_intent;
  private bool $should_create_stripe_payment_intent = false;

  /**
   * @param array<string, mixed> $args
   */
  public function __construct(array $args = []) {
    $this->bracket_product_utils =
      $args['bracket_product_utils'] ?? new BracketProductUtils();
    try {
      $this->stripe =
        $args['stripe_client'] ??
        new StripeClient(defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : '');
    } catch (InvalidArgumentException $e) {
      error_log('Stripe API key not set');
      $this->stripe = $args['stripe_client'] ?? new StripeClient();
    }
  }

  public function filter_request_params(array $data): array {
    $this->should_create_stripe_payment_intent =
      isset(
        $data[self::$SHOULD_CREATE_STRIPE_PAYMENT_INTENT_REQUEST_DATA_KEY]
      ) &&
      $data[self::$SHOULD_CREATE_STRIPE_PAYMENT_INTENT_REQUEST_DATA_KEY] ===
        true;

    return $data;
  }

  public function filter_after_play_added(BracketPlay $play): BracketPlay {
    if ($this->should_create_payment_intent_for_play($play)) {
      $intent = $this->create_payment_intent_for_paid_tournament_play($play);
      $this->stripe_payment_intent = $intent;
      $this->set_play_payment_intent_id($play->id, $intent->id);
    }
    return $play;
  }

  /**
   * @param array<mixed> $data
   * @return array<mixed>
   */
  public function filter_after_play_serialized(array $data): array {
    if (!isset($this->stripe_payment_intent)) {
      return $data;
    }
    $data[self::$CLIENT_SECRET_RESPONSE_DATA_KEY] =
      $this->stripe_payment_intent->client_secret;
    $data[self::$INTENT_ID_KEY] = $this->stripe_payment_intent->id;
    return $data;
  }

  public function set_play_payment_intent_id(
    int $play_post_id,
    string $payment_intent_id
  ): void {
    update_post_meta(
      $play_post_id,
      self::$PAYMENT_INTENT_ID_META_KEY,
      $payment_intent_id
    );
  }

  public function get_play_payment_intent_id(int $play_post_id): ?string {
    return get_post_meta(
      $play_post_id,
      self::$PAYMENT_INTENT_ID_META_KEY,
      true
    );
  }

  public function should_create_payment_intent_for_play(
    BracketPlay $play
  ): bool {
    return $this->should_create_stripe_payment_intent &&
      $this->requires_payment($play);
  }

  public function requires_payment(BracketPlay $play): bool {
    return $this->bracket_product_utils->has_bracket_fee($play->bracket_id);
  }

  public function create_payment_intent_for_paid_tournament_play(
    BracketPlay $play
  ): \Stripe\PaymentIntent {
    $fee = $this->bracket_product_utils->get_bracket_fee($play->bracket_id);
    $intent = $this->stripe->paymentIntents->create([
      'amount' => $fee * 100,
      'currency' => 'usd',
      'metadata' => [
        'bracket_id' => $play->bracket_id,
        'play_id' => $play->id,
      ],
    ]);
    return $intent;
  }
}
