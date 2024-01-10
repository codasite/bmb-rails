<?php
namespace WStrategies\BMB\Includes\Service\PaymentProcessors;

use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use WStrategies\BMB\Includes\Repository\BracketPlayRepo;

class StripeWebhookService {
  private BracketPlayRepo $play_repo;
  private string $webhook_secret;
  private StripeWebhookFunctions $stripe_webhook_functions;

  public function __construct($args = []) {
    $this->play_repo = new BracketPlayRepo();
    $api_key = defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : '';
    Stripe::setApiKey($api_key);
    $this->webhook_secret = defined('STRIPE_WEBHOOK_SECRET')
      ? STRIPE_WEBHOOK_SECRET
      : '';
    $this->stripe_webhook_functions =
      $args['stripe_webhook_functions'] ?? new StripeWebhookFunctions();
  }

  /**
   * @throws SignatureVerificationException
   */
  public function process_webhook(string $payload, string $sig_header): void {
    $event = $this->stripe_webhook_functions->constructEvent(
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
