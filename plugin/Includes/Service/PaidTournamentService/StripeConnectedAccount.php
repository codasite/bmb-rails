<?php
namespace WStrategies\BMB\Includes\Service\PaidTournamentService;

use Stripe\Exception\InvalidArgumentException;
use Stripe\PaymentIntent;
use Stripe\StripeClient;
use WP_User;
use WStrategies\BMB\Includes\Controllers\ApiListeners\BracketPlayCreateListenerBase;
use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductUtils;

class StripeConnectedAccount {
  public static string $CONNECTED_ACCOUNT_ID_META_KEY = 'stripe_connected_account_id';
  public static float $APPLICATION_FEE_PERCENTAGE = 0.125;
  // The minimum application fee to charge in cents
  public static float $APPLICATION_FEE_MINIMUM = 100;
  private int|null $owner_id;

  /**
   * @param array<string, mixed> $args
   */
  public function __construct(array $args = []) {
    $this->owner_id = $args['owner_id'] ?? null;
  }

  public function set_owner_id(int $owner_id): void {
    $this->owner_id = $owner_id;
  }

  private function validate_owner_id(): void {
    if (is_null($this->owner_id) || $this->owner_id === 0) {
      throw new InvalidArgumentException('Owner ID not set');
    }
  }

  public function get_connected_account_id(): string {
    $this->validate_owner_id();
    // get user meta
    $acct_id = get_user_meta(
      $this->owner_id,
      self::$CONNECTED_ACCOUNT_ID_META_KEY,
      true
    );
    return $acct_id;
  }

  public function set_connected_account_id(string $acct_id): void {
    $this->validate_owner_id();
    update_user_meta(
      $this->owner_id,
      self::$CONNECTED_ACCOUNT_ID_META_KEY,
      $acct_id
    );
  }

  public function should_create_destination_charge(): bool {
    $this->validate_owner_id();
    $acct_id = $this->get_connected_account_id($this->owner_id);
    return !empty($acct_id);
  }

  /**
   * @param int $amount The total amount to charge in cents
   * @return int The application fee to charge in cents
   */
  public function calculate_application_fee(int $amount): int {
    return max(
      self::$APPLICATION_FEE_MINIMUM,
      (int) $amount * self::$APPLICATION_FEE_PERCENTAGE
    );
  }
}
