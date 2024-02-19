<?php

namespace WStrategies\BMB\Includes\Service\Stripe;

use InvalidArgumentException;
use Stripe\StripeClient;
use WStrategies\BMB\Includes\Utils;

class StripeClientFactory {
  public function createStripeClient($args = []): StripeClient {
    try {
      return
        $args['stripe_client'] ??
        new StripeClient(defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : '');
    } catch (InvalidArgumentException $e) {
      if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
        (new Utils())->log_error(
          'Stripe API key not set',
        );
      }
      return new StripeClient();
    }
  }
}
