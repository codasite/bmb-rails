<?php
namespace WStrategies\BMB\Includes\Service\PaidTournamentService;

use Stripe\Account;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use WP_User;
use WStrategies\BMB\Includes\Service\Logger\SentryLogger;
use WStrategies\BMB\Includes\Service\Stripe\StripeClientFactory;
use WStrategies\BMB\Public\Partials\dashboard\DashboardPage;
use WStrategies\BMB\Public\Partials\StripeOnboardingRedirect;

class StripeConnectedAccount {
  public static string $CONNECTED_ACCOUNT_ID_META_KEY = 'stripe_connected_account_id';
  public static float $APPLICATION_FEE_PERCENTAGE = 0.125;
  // The minimum application fee to charge in cents
  public static float $APPLICATION_FEE_MINIMUM = 100;
  private StripeClient $stripe;
  private ?Account $stripe_account;
  private int $user_id;
  /**
   * @param array<string, mixed> $args
   */
  public function __construct(array $args = []) {
    $this->user_id = $args['user_id'];
    $this->stripe =
      $args['stripe_client'] ??
      (new StripeClientFactory())->createStripeClient();
  }

  /**
   * @param int $amount The total amount to charge in cents
   * @return int The application fee to charge in cents
   */
  static function calculate_application_fee(int $amount): int {
    return (int) max(
      self::$APPLICATION_FEE_MINIMUM,
      $amount * self::$APPLICATION_FEE_PERCENTAGE
    );
  }

  /**
   * @throws ApiErrorException|StripeConnectedAccountException
   */
  public function get_onboarding_link(): string {
    $acct_id = $this->get_or_create_account_id();
    $res = $this->stripe->accountLinks->create([
      'account' => $acct_id,
      'refresh_url' => StripeOnboardingRedirect::get_url(),
      'return_url' => DashboardPage::get_url(),
      'type' => 'account_onboarding',
    ]);
    return $res->url;
  }

  /**
   * @throws ApiErrorException
   */
  public function get_onboarding_or_login_link(): string {
    if ($this->charges_enabled()) {
      $res = $this->stripe->accounts->createLoginLink($this->get_account_id());
      return $res->url;
    }
    return $this->get_onboarding_link();
  }

  /**
   * @throws ApiErrorException
   * @throws StripeConnectedAccountException
   */
  public function get_or_create_account_id(): string {
    if ($this->has_account()) {
      return $this->get_account_id();
    } else {
      $acct_id = $this->create_account();
      $this->set_account_id($acct_id);
      return $acct_id;
    }
  }

  /**
   * @throws ApiErrorException
   * @throws StripeConnectedAccountException
   */
  public function create_account(): string {
    $user = new WP_User($this->user_id);
    $email = $user->user_email;
    if (empty($email)) {
      throw new StripeConnectedAccountException('User email not set');
    }
    $res = $this->stripe->accounts->create([
      'type' => 'express',
      'email' => $email,
    ]);
    return $res->id;
  }

  public function get_account_id(): string {
    return get_user_meta(
      $this->user_id,
      self::$CONNECTED_ACCOUNT_ID_META_KEY,
      true
    );
  }

  public function has_account(): bool {
    return $this->get_stripe_account() !== null;
  }

  public function set_account_id(string $acct_id): void {
    update_user_meta(
      $this->user_id,
      self::$CONNECTED_ACCOUNT_ID_META_KEY,
      $acct_id
    );
  }

  public function should_create_destination_charge(): bool {
    return $this->charges_enabled();
  }

  public function charges_enabled(): bool {
    if (!$this->has_account()) {
      return false;
    }
    return $this->get_stripe_account()->charges_enabled;
  }

  public function get_stripe_account() {
    if (isset($this->stripe_account)) {
      return $this->stripe_account;
    }
    $acct_id = $this->get_account_id();
    if (empty($acct_id)) {
      return null;
    }
    try {
      $this->stripe_account = $this->stripe->accounts->retrieve($acct_id);
      return $this->stripe_account;
    } catch (ApiErrorException $e) {
      return null;
    }
  }
}
