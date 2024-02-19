<?php

use Stripe\PaymentIntent;
use Stripe\Service\PaymentIntentService;
use Stripe\StripeClient;
use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductUtils;
use WStrategies\BMB\Includes\Service\PaidTournamentService\StripeConnectedAccount;
use WStrategies\BMB\Includes\Service\PaidTournamentService\StripePaidTournamentService;
class StripeConnectedAccountTest extends TestCase {
  public function test_create_connected_account() {
    $user = $this->create_user();
    $stripe_mock = $this->getMockBuilder(StripeClient::class)
      ->disableOriginalConstructor()
      ->getMock();

    $stripe_accounts_mock = $this->getMockBuilder(AccountService::class)
      ->disableOriginalConstructor()
      ->getMock();

    $stripe_accounts_mock
      ->expects($this->once())
      ->method('create')
      ->with([
        'type' => 'express',
        'email' => $user->user_email,
      ])
      ->willReturn((object) ['id' => 'acct_1']);

    $stripe_mock->accounts = $stripe_accounts_mock;

    $service = new StripeConnectedAccount([
      'owner_id' => $user->ID,
      'stripe_client' => $stripe_mock,
    ]);
    $acct_id = $service->create_or_get_connected_account_id();
    $this->assertEquals('acct_1', $acct_id);
  }
}
