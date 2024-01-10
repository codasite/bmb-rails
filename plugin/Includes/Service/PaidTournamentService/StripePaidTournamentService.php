<?php
namespace WStrategies\BMB\Includes\Service\PaidTournamentService;

use Stripe\Exception\ApiErrorException;
use Stripe\Exception\InvalidArgumentException;
use Stripe\PaymentIntent;
use Stripe\StripeClient;
use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductUtils;

class StripePaidTournamentService implements PaidTournamentServiceInterface {
  public static string $PAYMENT_INTENT_ID_META_KEY = 'payment_intent_id';
  public static string $CLIENT_SECRET_RESPONSE_DATA_KEY = 'stripe_payment_intent_client_secret';

  private StripeClient $stripe;
  private BracketProductUtils $bracket_product_utils;
  // This is set after the payment intent is created for a paid play,
  // and then the client secret appended to the play response data
  private PaymentIntent $stripe_payment_intent;

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

  public function on_play_created(BracketPlay $play): void {
    if (!$this->requires_payment($play)) {
      return;
    }
    $intent = $this->create_payment_intent_for_paid_tournament_play($play);
    $this->stripe_payment_intent = $intent;
    $this->set_play_payment_intent_id($play->id, $intent->id);
  }

  /**
   * @param array<mixed> $data
   * @return array<mixed>
   */
  public function filter_play_created_response_data(array $data): array {
    $secret = isset($this->stripe_payment_intent)
      ? $this->stripe_payment_intent->client_secret
      : null;
    if ($secret) {
      $data[self::$CLIENT_SECRET_RESPONSE_DATA_KEY] = $secret;
    }
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

  /**
   * @throws ApiErrorException
   */
  public function get_play_payment_intent(
    int $play_post_id
  ): ?\Stripe\PaymentIntent {
    $payment_intent_id = $this->get_play_payment_intent_id($play_post_id);
    if (!$payment_intent_id) {
      return null;
    }
    return $this->stripe->paymentIntents->retrieve($payment_intent_id);
  }

  public function requires_payment(BracketPlay $play): bool {
    $fee = $this->bracket_product_utils->get_bracket_fee($play->bracket_id);
    return $fee > 0;
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
