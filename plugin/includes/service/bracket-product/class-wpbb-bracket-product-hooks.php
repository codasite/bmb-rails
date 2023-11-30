<?php
require_once WPBB_PLUGIN_DIR . 'includes/class-wpbb-loader.php';
require_once WPBB_PLUGIN_DIR . 'includes/class-wpbb-hooks-interface.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/product-integrations/class-wpbb-wc-functions.php';

const BRACKET_FEE_META_PREFIX = 'bracket_product_fee_meta_';

class Wpbb_BracketProductHooks implements Wpbb_HooksInterface {
  /**
   * @var Wpbb_BracketProductUtils
   */
  private $bracket_product_utils;

  /**
   * @var Wpbb_BracketRepo
   */
  private $bracket_repo;

  /**
   * @var Wpbb_BracketPlayRepo
   */
  private $play_repo;

  /**
   * @var Wpbb_WcFunctions
   */
  private $wc;

  public function __construct($args = []) {
    $this->bracket_product_utils =
      $args['bracket_product_utils'] ?? new Wpbb_BracketProductUtils();
    $this->bracket_repo = $args['bracket_repo'] ?? new Wpbb_BracketRepo();
    $this->play_repo = $args['play_repo'] ?? new Wpbb_BracketPlayRepo();
    $this->wc = $args['wc'] ?? new Wpbb_WcFunctions();
  }

  public function load(Wpbb_Loader $loader): void {
    $loader->add_action(
      'woocommerce_cart_calculate_fees',
      [$this, 'add_paid_bracket_fee_to_cart'],
      10,
      1
    );
    $loader->add_action(
      'woocommerce_checkout_create_order_line_item',
      [$this, 'add_fee_meta_to_order_item'],
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
        $play = $this->play_repo->get($config->play_id);

        // do not add fees to printed plays
        if (empty($bracket_id) || empty($play) || $play->is_printed) {
          continue;
        }

        $fee_amount = $this->bracket_product_utils->get_bracket_fee(
          $bracket_id
        );
        if ($fee_amount > 0) {
          $fee_name = $this->bracket_product_utils->get_bracket_fee_name(
            $bracket_id
          );
          $fee_id = sanitize_title($fee_name);
          $fee = $cart->fees_api()->add_fee([
            'id' => $fee_id,
            'name' => $fee_name,
            'amount' => $fee_amount,
            'taxable' => false,
            'tax_class' => '',
          ]);
          if (!is_wp_error($fee)) {
            $this->wc->session_set(BRACKET_FEE_META_PREFIX . $bracket_id, [
              'fee_amount' => $fee_amount,
            ]);
          }
        }
      }
    }
  }

  // Add the fee data as meta on the order item.
  // This allows order exports to list the fee individually for each item under one header.
  // This hooks into `woocommerce_checkout_create_order_line_item` action
  public function add_fee_meta_to_order_item(
    $item,
    $cart_item_key,
    $values,
    $order
  ) {
    $product = $item->get_product();
    if (
      !$this->bracket_product_utils->is_bracket_product($product) ||
      !array_key_exists('bracket_config', $values)
    ) {
      return;
    }

    $bracket_id = $values['bracket_config']->bracket_id;
    // get the fee meta from the session
    $fee_meta = $this->wc->session_get(BRACKET_FEE_META_PREFIX . $bracket_id);
    if (empty($fee_meta)) {
      return;
    }

    $bracket_fee = $fee_meta['fee_amount'];
    $item->add_meta_data('bracket_fee', floatval($bracket_fee), true);

    // remove the fee meta from the session
    // this prevents the fee meta from being added to more than one order item for the same bracket
    $this->wc->session_unset(BRACKET_FEE_META_PREFIX . $bracket_id);
  }
}
