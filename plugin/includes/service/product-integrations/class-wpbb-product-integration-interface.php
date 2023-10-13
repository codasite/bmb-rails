<?php
require_once WPBB_PLUGIN_DIR .
  'includes/service/product-integrations/class-wpbb-product-integration-interface.php';

interface Wpbb_ProductIntegrationInterface {
  // admin hooks
  public function after_variable_attributes(
    $loop,
    $variation_data,
    $variation
  ): void;

  public function save_product_variation($variation_id, $i): void;

  public function admin_notices(): void;

  // public hooks
  public function add_to_cart_validation(
    $passed,
    $product_id,
    $quantity,
    $variation_id = null,
    $variations = null
  ): bool;

  public function add_cart_item_data(
    $cart_item_data,
    $product_id,
    $variation_id
  ): array;

  public function checkout_create_order_line_item(
    $item,
    $cart_item_key,
    $values,
    $order
  ): void;

  public function before_checkout_process(): void;

  public function payment_complete($order_id): void;

  public function available_variation(
    $available_array,
    $this_obj,
    $variation
  ): array;

  public function generate_images(Wpbb_PostBracketInterface $bracket): void;
  public function get_image_config(Wpbb_PostBracketInterface $bracket): array;
  // product preview
}
