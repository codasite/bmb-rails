<?php

use Stripe\StripeClient;
use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductUtils;
use WStrategies\BMB\Includes\Service\PaymentProcessors\StripePayments;

class StripePaidTournamentService {
  private StripePayments $stripe_payments;
  private StripeClient $stripe;
  private BracketProductUtils $bracket_product_utils;
  public static $PAYMENT_INTENT_ID_META_KEY = 'payment_intent_id';

  public function __construct(array $args = []) {
    $this->stripe_payments = $args['stripe_payments'] ?? new StripePayments();
    $this->stripe =
      $args['stripe_client'] ??
      new StripeClient(defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : '');
  }

  public function on_play_created(BracketPlay $play): void {
    if (!$this->requires_payment($play)) {
      return;
    }
    $intent = $this->create_payment_intent_for_paid_tournament_play($play);
    // update the play post meta with the payment intent id
    $this->set_play_payment_intent_id($play->id, $intent->id);
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

  public function requires_payment(BracketPlay $play): bool {
    if ($play->bracket->fee > 0) {
      return true;
    }
    return false;
  }

  public function create_payment_intent_for_paid_tournament_play(
    BracketPlay $play
  ): \Stripe\PaymentIntent {
    $fee = $play->bracket
      ? $play->bracket->fee
      : $this->bracket_product_utils->get_bracket_fee($play->bracket_id);
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
