<?php

namespace WStrategies\BMB\Includes\Hooks;

use WStrategies\BMB\Includes\Service\PaidTournamentService\StripeConnectedAccountFactory;

class UserAdminHooks implements HooksInterface {
  private StripeConnectedAccountFactory $connected_account_factory;

  public function __construct($opts = []) {
    $this->connected_account_factory =
      $opts['connected_account_factory'] ?? new StripeConnectedAccountFactory();
  }

  public function load(Loader $loader): void {
    $loader->add_action('show_user_profile', [
      $this,
      'display_stripe_connected_acct_meta_box',
    ]);
    $loader->add_action('edit_user_profile', [
      $this,
      'display_stripe_connected_acct_meta_box',
    ]);
    $loader->add_action('user_new_form', [
      $this,
      'display_stripe_connected_acct_meta_box',
    ]);
    $loader->add_action('personal_options_update', [
      $this,
      'save_stripe_connected_acct_meta_box',
    ]);
    $loader->add_action('edit_user_profile_update', [
      $this,
      'save_stripe_connected_acct_meta_box',
    ]);
    $loader->add_action('user_register', [
      $this,
      'save_stripe_connected_acct_meta_box',
    ]);
  }

  public function display_stripe_connected_acct_meta_box($user): void {
    $account = $this->connected_account_factory->get_account($user->ID);
    wp_nonce_field(
      'stripe_connected_acct_meta_box',
      'stripe_connected_acct_meta_box_nonce'
    );
    ?>
    <h2>Stripe Connected Account</h2>
    <table class="form-table">
      <tbody>
        <tr>
          <th scope="row">
            <label for="acct_id">Connected Account ID</label>
          </th>
          <td>
            <input
              type="text"
              name="acct_id"
              id="acct_id"
              value="<?php echo esc_attr($account->get_account_id()); ?>"
              class="regular-text ltr"
            />
          </td>
        </tr>
      </tbody>
    </table>
		<?php
  }

  public function save_stripe_connected_acct_meta_box($user_id): void {
    if (!current_user_can('edit_user', $user_id)) {
      return;
    }
    if (
      !isset($_POST['stripe_connected_acct_meta_box_nonce']) ||
      !wp_verify_nonce(
        $_POST['stripe_connected_acct_meta_box_nonce'],
        'stripe_connected_acct_meta_box'
      )
    ) {
      return;
    }
    if (isset($_POST['acct_id'])) {
      $account = $this->connected_account_factory->get_account($user_id);
      $account->set_account_id(sanitize_text_field($_POST['acct_id']));
    }
  }
}
