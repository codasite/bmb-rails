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

  public function getAccount(int $userId): StripeConnectedAccount {
    return new StripeConnectedAccount([
      'stripe_client' => $this->stripe,
      'owner_id' => $userId,
    ]);
  }

  public function getAccountForCurrentUser(): StripeConnectedAccount {
    return new StripeConnectedAccount([
      'stripe_client' => $this->stripe,
      'owner_id' => get_current_user_id(),
    ]);
  }
}
