<?php

namespace WStrategies\BMB\Includes\Service\PaidTournamentService;

use Stripe\StripeClient;
use WStrategies\BMB\Includes\Service\Stripe\StripeClientFactory;

class StripeConnectedAccountFactory {
  private StripeClient $stripe;
  public function __construct(array $args = []) {
    $this->stripe =
      $args['stripe_client'] ??
      (new StripeClientFactory())->createStripeClient();
  }

  public function get_account(int $userId): StripeConnectedAccount {
    return new StripeConnectedAccount([
      'stripe_client' => $this->stripe,
      'user_id' => $userId,
    ]);
  }

  public function get_account_for_current_user(): StripeConnectedAccount {
    return self::get_account(get_current_user_id());
  }
}
