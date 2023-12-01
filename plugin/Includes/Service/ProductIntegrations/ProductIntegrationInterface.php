<?php
namespace WStrategies\BMB\Includes\Service\ProductIntegrations;

use WStrategies\BMB\Includes\Domain\PostBracketInterface;

interface ProductIntegrationInterface {
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

  public function get_post_meta_key(): string;

  public function generate_images(PostBracketInterface $bracket): void;

  /**
   * Given a placement ('top' or 'center') returns an overlay map that can get passed direcly to the bracket preview page
   *
   * @var PostBracketInterface $bracket
   * @var string $placement - 'top' or 'center'
   *
   * @return array - an array of overlay maps
   *
   * @example
   * [
   * 'light' => 'someS3url',
   * 'dark' => 'someS3url'
   * ]
   */
  public function get_overlay_map(
    PostBracketInterface $bracket,
    string $placement
  ): array;
}
