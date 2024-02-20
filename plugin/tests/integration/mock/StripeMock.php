<?php
namespace WStrategies\BMB\tests\Includes\Service\PaymentProcessors;

use Stripe\Account;
use Stripe\Service\AccountLinkService;
use Stripe\Service\AccountService;
use Stripe\Service\PaymentIntentService;
use Stripe\StripeClient;

class StripeMock extends StripeClient {
  public PaymentIntentService $paymentIntents;
  public AccountService $accounts;
  public AccountLinkService $accountLinks;
}

class StripeAccountMock extends Account {
  public bool $charges_enabled;
}
