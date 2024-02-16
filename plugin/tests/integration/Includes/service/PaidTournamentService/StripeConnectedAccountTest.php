
<?php
use WStrategies\BMB\Includes\Service\PaidTournamentService\StripeConnectedAccount;

class StripeConnectedAccountTest extends WPBB_UnitTestCase {
  public function test_get_connected_account_id() {
    $user = $this->create_user();
    update_user_meta(
      $user->ID,
      StripeConnectedAccount::$CONNECTED_ACCOUNT_ID_META_KEY,
      'acct_1'
    );

    $service = new StripeConnectedAccount(['owner_id' => $user->ID]);
    $acct_id = $service->get_connected_account_id();
    $this->assertEquals('acct_1', $acct_id);
  }

  public function test_set_connected_account_id() {
    $user = $this->create_user();
    $service = new StripeConnectedAccount(['owner_id' => $user->ID]);
    $service->set_connected_account_id('acct_1');
    $acct_id = get_user_meta(
      $user->ID,
      StripeConnectedAccount::$CONNECTED_ACCOUNT_ID_META_KEY,
      true
    );
    $this->assertEquals('acct_1', $acct_id);
  }

  public function test_calculate_application_fee() {
    $user = $this->create_user();
    $service = new StripeConnectedAccount(['owner_id' => $user->ID]);
    $fee = $service->calculate_application_fee(1000);
    $this->assertEquals(125, $fee);
  }

  public function test_calculate_application_fee_minimum() {
    $user = $this->create_user();
    $service = new StripeConnectedAccount(['owner_id' => $user->ID]);
    $fee = $service->calculate_application_fee(100);
    $this->assertEquals(100, $fee);
  }
}

