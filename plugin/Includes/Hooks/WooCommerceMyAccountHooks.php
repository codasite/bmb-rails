<?php
namespace WStrategies\BMB\Includes\Hooks;

use WStrategies\BMB\Public\Partials\MyAccount\DeleteAccount;

class WooCommerceMyAccountHooks implements HooksInterface {
  public function load(Loader $loader): void {
    // Add new menu item to My Account menu
    $loader->add_filter('woocommerce_account_menu_items', [
      $this,
      'add_delete_account_menu_item',
    ]);

    // Register new endpoint
    $loader->add_action('init', [$this, 'add_delete_account_endpoint']);

    // Add content for the endpoint
    $loader->add_action('woocommerce_account_delete-account_endpoint', [
      $this,
      'delete_account_content',
    ]);

    // Handle account deletion
    $loader->add_action('template_redirect', [
      $this,
      'handle_account_deletion',
    ]);
  }

  public function add_delete_account_menu_item($menu_items): array {
    // Add new menu item at the end
    $menu_items['delete-account'] = 'Delete Account';
    return $menu_items;
  }

  public function add_delete_account_endpoint(): void {
    add_rewrite_endpoint('delete-account', EP_ROOT | EP_PAGES);
  }

  public function delete_account_content(): void {
    echo (new DeleteAccount())->render();
  }

  public function handle_account_deletion(): void {
    if (
      !is_account_page() ||
      !isset($_POST['delete_account_submit']) ||
      !isset($_POST['delete_confirmation']) ||
      !isset($_POST['delete_account_nonce'])
    ) {
      return;
    }

    // Verify nonce
    if (!wp_verify_nonce($_POST['delete_account_nonce'], 'delete_account')) {
      wc_add_notice('Invalid request.', 'error');
      return;
    }

    // Verify confirmation text
    if ($_POST['delete_confirmation'] !== 'DELETE') {
      wc_add_notice(
        'Please type "DELETE" to confirm account deletion.',
        'error'
      );
      return;
    }

    // Include required file for wp_delete_user function
    require_once ABSPATH . 'wp-admin/includes/user.php';

    // Get current user
    $current_user = wp_get_current_user();
    if (!$current_user->exists()) {
      return;
    }

    // Delete the user
    if (
      function_exists('wp_delete_user') &&
      wp_delete_user($current_user->ID)
    ) {
      wp_logout();
      wp_safe_redirect(home_url());
      exit();
    } else {
      wc_add_notice('Error deleting account. Please try again.', 'error');
    }
  }
}
