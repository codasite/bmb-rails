<?php
require_once WPBB_PLUGIN_DIR . 'includes/class-wpbb-loader.php';
require_once WPBB_PLUGIN_DIR . 'includes/class-wpbb-hooks-interface.php';

class Wpbb_BracketProductHooks implements Wpbb_HooksInterface {
  /**
   * The loader that's responsible for maintaining and registering all hooks that power
   * the plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      Wpbb_Loader $loader Maintains and registers all hooks for the plugin.
   */
  private $loader;

  /**
   * @var Wpbb_BracketProductUtils
   */
  private $bracket_product_utils;

  /**
   * @var Wpbb_BracketRepo
   */
  private $bracket_repo;

  /**
   * @var Wpbb_Utils
   */
  private $utils;

  public function __construct($args = []) {
    $this->loader = $args['loader'] ?? new Wpbb_Loader();
    $this->bracket_product_utils =
      $args['bracket_product_utils'] ?? new Wpbb_BracketProductUtils();
    $this->bracket_repo = $args['bracket_repo'] ?? new Wpbb_BracketRepo();
    $this->utils = $args['utils'] ?? new Wpbb_Utils();
  }

  public function load(): void {
    $this->define_hooks();
  }

  private function define_hooks(): void {
    $this->loader->add_action(
      'woocommerce_cart_calculate_fees',
      $this,
      'add_paid_bracket_fee_to_cart',
      10,
      1
    );
    $this->loader->add_action(
      'woocommerce_checkout_create_order_line_item',
      $this,
      'add_fee_meta_to_order_item',
      10,
      4
    );
  }

  // This hooks into `woocommerce_cart_calculate_fees` action
  public function add_paid_bracket_fee_to_cart($cart) {
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
      $product = $cart_item['data'];
      if ($this->bracket_product_utils->is_bracket_product($product)) {
        $config = $cart_item['bracket_config'] ?? null;
        if (!$config) {
          continue;
        }
        $bracket_id = $config->bracket_id;
        if (empty($bracket_id)) {
          continue;
        }
        $fee_amount = $this->bracket_product_utils->get_bracket_fee(
          $bracket_id
        );
        if ($fee_amount > 0) {
          $fee_name = $this->bracket_product_utils->get_bracket_fee_name(
            $bracket_id
          );
          $cart->add_fee($fee_name, $fee_amount, false, '');
        }
      }
    }
  }

  // Add the fee data as meta on the order item.
  // This is needed in case the bracket title, which the fee name is derived from, is changed.
  // This hooks into `woocommerce_checkout_create_order_line_item` action
  public function add_fee_meta_to_order_item(
    $item,
    $cart_item_key,
    $values,
    $order
  ) {
    if (!array_key_exists('bracket_id', $values)) {
      return;
    }
    $bracket_id = $values['bracket_id'];
    $fee_amount = $this->bracket_product_utils->get_bracket_fee($bracket_id);
    if ($fee_amount > 0) {
      $fee_name = $this->bracket_product_utils->get_bracket_fee_name(
        $bracket_id
      );
      // get the fees from the order
      $fees = $order->get_fees();
      // get the fee that matches the bracket fee
      $fee = array_filter($fees, function ($fee) use ($fee_name) {
        return $fee->name === $fee_name;
      });
      // if there is a fee that matches the bracket fee, add the meta data
      if (!empty($fee)) {
        $item->add_meta_data('bracket_fee', $fee_amount);
      }
    }
  }
}
