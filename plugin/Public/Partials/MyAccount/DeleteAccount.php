<?php

namespace WStrategies\BMB\Public\Partials\MyAccount;

use WStrategies\BMB\Public\Partials\TemplateInterface;

class DeleteAccount implements TemplateInterface {
  public function render(): false|string {
    ob_start(); ?>
<div class="woocommerce-delete-account">
    <p class="woocommerce-info woocommerce-message--warning">
        <?php esc_html_e(
          'Warning: This action cannot be undone. All your data will be permanently deleted.',
          'wp-bracket-builder'
        ); ?>
    </p>

		<form class="woocommerce-delete-account-form" method="post">
        <?php wp_nonce_field('delete_account', 'delete_account_nonce'); ?>
        
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="password">
                <?php esc_html_e('Current Password:', 'wp-bracket-builder'); ?>
            </label>
            <input type="password" 
                   class="woocommerce-Input woocommerce-Input--text input-text" 
                   name="current_password" 
                   id="password" 
                   required />
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="delete_account_confirmation">
                <?php esc_html_e(
                  'Type "DELETE" to confirm:',
                  'wp-bracket-builder'
                ); ?>
            </label>
            <input type="text" 
                   class="woocommerce-Input woocommerce-Input--text input-text" 
                   name="delete_confirmation" 
                   id="delete_account_confirmation" 
                   autocomplete="off"
                   required />
            <input type="hidden" name="expected_confirmation" value="DELETE" />
        </p>

        <p class="woocommerce-form-row form-row">
            <button type="submit" 
                    class="woocommerce-Button button btn-danger" 
                    name="delete_account_submit" 
                    value="<?php esc_attr_e(
                      'Delete Account',
                      'wp-bracket-builder'
                    ); ?>">
                <?php esc_html_e('Delete Account', 'wp-bracket-builder'); ?>
            </button>
        </p>
    </form>
</div>
<?php return ob_get_clean();
  }
}
