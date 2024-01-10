<?php
namespace WStrategies\BMB\Includes\Service\PaymentProcessors;

use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\StripeClient;
use WStrategies\BMB\Includes\Repository\BracketPlayRepo;
use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductUtils;

class StripePayments {
  private BracketProductUtils $bracket_product_utils;
  private StripeClient $stripe;
  private BracketPlayRepo $play_repo;
  private string $webhook_secret;

  /**
   * @param array{stripe_client?: StripeClient} $args
   */
  public function __construct(array $args = []) {
    $this->bracket_product_utils = new BracketProductUtils();
    $this->play_repo = new BracketPlayRepo();
    $api_key = defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : '';
    $this->stripe = $args['stripe_client'] ?? new StripeClient($api_key);
    Stripe::setApiKey($api_key);
    $this->webhook_secret = defined('STRIPE_WEBHOOK_SECRET')
      ? STRIPE_WEBHOOK_SECRET
      : '';
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

  /**
   * @throws SignatureVerificationException
   */
  public function process_webhook(string $payload, string $sig_header): void {
    $event = \Stripe\Webhook::constructEvent(
      $payload,
      $sig_header,
      $this->webhook_secret
    );
    switch ($event->type) {
      case 'payment_intent.succeeded':
        $paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent
        $this->handlePaymentIntentSucceeded($paymentIntent);
        break;
      default:
    }
  }

  private function handlePaymentIntentSucceeded(
    PaymentIntent $paymentIntent
  ): void {
    $play_id = $paymentIntent->metadata['play_id'];
    $play = $this->play_repo->get($play_id);
    $this->play_repo->update($play, [
      'is_paid' => true,
    ]);
  }
}
