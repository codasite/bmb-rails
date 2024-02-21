<?php

namespace WStrategies\BMB\Includes\Service\PaidTournamentService;

class StubStripeConnectedAccountFactory extends StripeConnectedAccountFactory {
  /**
   * @var StripeConnectedAccount
   */
  private $stub_account;

  public function __construct(array $args = []) {
    parent::__construct($args);
    $this->stub_account = new class (['user_id' => 1]) extends
      StripeConnectedAccount {
      public function charges_enabled(): bool {
        return false;
      }
    };
  }

  public function get_account_for_current_user(): StripeConnectedAccount {
    return $this->stub_account;
  }

  public function get_account(int $userId): StripeConnectedAccount {
    return $this->stub_account;
  }
}
