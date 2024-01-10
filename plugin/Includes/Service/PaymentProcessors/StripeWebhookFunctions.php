<?php
namespace WStrategies\BMB\Includes\Service\PaymentProcessors;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookFunctions {
  /**
   * @throws SignatureVerificationException
   */
  function constructEvent($payload, $sig_header, $webhook_secret) {
    return \Stripe\Webhook::constructEvent(
      $payload,
      $sig_header,
      $webhook_secret
    );
  }
}
