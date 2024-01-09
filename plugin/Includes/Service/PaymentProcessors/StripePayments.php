<?php
namespace WStrategies\BMB\Includes\Service\PaymentProcessors;

use Stripe\StripeClient;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductUtils;

class StripePayments {
  private $bracket_repo;
  private $bracket_product_utils;
  private StripeClient $stripe;

  public function __construct() {
    $this->bracket_repo = new BracketRepo();
    $this->bracket_product_utils = new BracketProductUtils();
    $this->stripe = new StripeClient(STRIPE_SECRET_KEY);
  }

  public function create_payment_intent_for_paid_bracket(
    int $bracket_id
  ): ?string {
    $bracket = $this->bracket_repo->get($bracket_id);
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
