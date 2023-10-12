<?php
require_once plugin_dir_path(dirname(__FILE__)) .
  'class-wpbb-product-integration-interface.php';
require_once 'class-wpbb-gelato-admin-hooks.php';
require_once 'class-wpbb-gelato-public-hooks.php';

class Wpbb_GelatoProductIntegration implements
  Wpbb_ProductIntegrationInterface {
  /**
   * @var Wpbb_GelatoAdminHooks
   */
  private $admin_hooks;

  /**
   * @var Wpbb_GelatoPublicHooks
   */
  private $public_hooks;

  public function __construct() {
    $this->admin_hooks = new Wpbb_GelatoAdminHooks();
    $this->public_hooks = new Wpbb_GelatoPublicHooks();
  }

  // Admin hooks
  public function after_variable_attributes(
    $loop,
    $variation_data,
    $variation
  ): void {
    $this->admin_hooks->variation_settings_fields(
      $loop,
      $variation_data,
      $variation
    );
  }

  public function save_product_variation($variation_id, $i): void {
    $this->admin_hooks->validate_variation_fields($variation_id, $i);
    $this->admin_hooks->save_variation_settings_fields($variation_id, $i);
  }

  public function admin_notices(): void {
    $this->admin_hooks->display_custom_admin_error();
  }

  // Public hooks
  public function add_to_cart_validation(
    $passed,
    $product_id,
    $quantity,
    $variation_id = null,
    $variations = null
  ): bool {
    return $this->public_hooks->bracket_product_add_to_cart_validation(
      $passed,
      $product_id,
      $quantity,
      $variation_id,
      $variations
    );
  }

  public function add_cart_item_data(
    $cart_item_data,
    $product_id,
    $variation_id
  ): array {
    return $this->public_hooks->add_bracket_to_cart_item_data(
      $cart_item_data,
      $product_id,
      $variation_id
    );
  }

  public function checkout_create_order_line_item(
    $item,
    $cart_item_key,
    $values,
    $order
  ): void {
    $this->public_hooks->add_bracket_to_order_item(
      $item,
      $cart_item_key,
      $values,
      $order
    );
  }

  public function before_checkout_process(): void {
    $this->public_hooks->handle_before_checkout_process();
  }

  public function payment_complete($order_id): void {
    $this->public_hooks->handle_payment_complete($order_id);
  }

  public function available_variation(
    $available_array,
    $this_obj,
    $variation
  ): array {
    return $this->public_hooks->filter_variation_availability(
      $available_array,
      $this_obj,
      $variation
    );
  }
}
