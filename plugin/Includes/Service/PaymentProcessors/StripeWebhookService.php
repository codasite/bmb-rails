<?php
namespace WStrategies\BMB\Includes\Service\PaymentProcessors;

use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Service\TournamentEntryService;

class StripeWebhookService {
  private PlayRepo $play_repo;
  private TournamentEntryService $tournament_entry_service;
  private string $webhook_secret;
  private StripeWebhookFunctions $stripe_webhook_functions;

  public function __construct($args = []) {
    $this->play_repo = $args['play_repo'] ?? new PlayRepo();
    $this->tournament_entry_service =
      $args['tournament_entry_service'] ?? new TournamentEntryService();
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
   * @throws \Exception
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

  /**
   * @throws \Exception
   */
  private function handlePaymentIntentSucceeded(
    PaymentIntent $paymentIntent
  ): void {
    $message =
      'Attempting to handle payment intent succeeded and set play to paid. ';
    $play_id = $paymentIntent->metadata['play_id'];
    if (!$play_id) {
      throw new \Exception(
        $message . 'No play_id found in payment intent metadata'
      );
    }
    $play = $this->play_repo->get($play_id);
    if (!$play) {
      throw new \Exception($message . 'No play found with id ' . $play_id);
    }
    $paid_play = $this->play_repo->update($play, [
      'is_paid' => true,
    ]);
    if (!$paid_play || !$paid_play->is_paid) {
      throw new \Exception(
        $message . 'Failed to update play with id ' . $play_id . ' to paid'
      );
    }
    $entry = $this->tournament_entry_service->try_mark_play_as_tournament_entry(
      $paid_play
    );
    if (!$entry || !$entry->is_tournament_entry) {
      throw new \Exception(
        $message .
          'Failed to mark play with id ' .
          $play_id .
          ' as tournament entry'
      );
    }
  }
}
