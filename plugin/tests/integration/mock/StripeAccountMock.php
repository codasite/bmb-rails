<?php

namespace WStrategies\BMB\tests\integration\mock;

use Stripe\Account;

class StripeAccountMock extends Account {
  public bool $charges_enabled;
}
