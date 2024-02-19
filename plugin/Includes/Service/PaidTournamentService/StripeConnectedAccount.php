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
  private StripeClient $stripe;
  private int|null $owner_id;
  /**
   * @param array<string, mixed> $args
   */
  public function __construct(array $args = []) {
    $this->owner_id = $args['owner_id'] ?? null;
    try {
      $this->stripe =
        $args['stripe_client'] ??
        new StripeClient(defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : '');
    } catch (InvalidArgumentException $e) {
      error_log('Stripe API key not set');
      $this->stripe = $args['stripe_client'] ?? new StripeClient();
    }
  }

  public function set_owner_id(int $owner_id): void {
    $this->owner_id = $owner_id;
  }

  private function validate_owner_id(): void {
    if (is_null($this->owner_id) || $this->owner_id === 0) {
      throw new InvalidArgumentException('Owner ID not set');
    }
  }

  public function get_onboarding_link(): string {
    $this->validate_owner_id();
    $acct_id = $this->create_or_get_connected_account_id();
    if (empty($acct_id)) {
      throw new InvalidArgumentException('Connected account ID not set');
    }
    $res = $this->stripe->accountLinks->create([
      'account' => $acct_id,
      'refresh_url' => home_url('/account-link-refresh'),
      'return_url' => home_url('/account-link-return'),
      'type' => 'account_onboarding',
    ]);
    return $res->url;
  }

  public function create_or_get_connected_account_id(): string {
    $this->validate_owner_id();
    $acct_id = $this->get_connected_account_id();
    if (empty($acct_id)) {
      $acct_id = $this->create_connected_account();
      $this->set_connected_account_id($acct_id);
    }
    return $acct_id;
  }

  public function create_connected_account(): string {
    $this->validate_owner_id();
    $user = new WP_User($this->owner_id);
    $email = $user->user_email;
    if (empty($email)) {
      throw new InvalidArgumentException('User email not set');
    }
    $res = $this->stripe->accounts->create([
      'type' => 'express',
      'email' => $email,
    ]);
    return $res->id;
  }

  public function get_connected_account_id(): string {
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
