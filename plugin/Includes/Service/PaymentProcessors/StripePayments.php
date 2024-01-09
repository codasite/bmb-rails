<?php
namespace WStrategies\BMB\Includes\Service\PaymentProcessors;

use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductUtils;

class StripePayments {
  private BracketProductUtils $bracket_product_utils;
  private StripeClient $stripe;

  /**
   * @param array{stripe_client?: StripeClient} $args
   */
  public function __construct(array $args = []) {
    $this->bracket_product_utils = new BracketProductUtils();
    $this->stripe =
      $args['stripe_client'] ??
      new StripeClient(defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : '');
  }

  /**
   * @throws ApiErrorException
   */
  public function create_payment_intent_for_paid_bracket(
    int $bracket_id
  ): ?string {
    $fee = $this->bracket_product_utils->get_bracket_fee($bracket_id);
    $amount = $fee * 100;
    $intent = $this->stripe->paymentIntents->create([
      'amount' => $amount,
      'currency' => 'usd',
      'metadata' => [
        'bracket_id' => $bracket_id,
      ],
    ]);
    return $intent->client_secret;
  }
}
