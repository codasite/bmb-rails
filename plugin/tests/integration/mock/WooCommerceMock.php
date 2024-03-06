<?php
namespace WStrategies\BMB\tests\integration\mock;

interface OrderInterface {
  public function get_items();
  public function get_id();
  public function get_user_id();
  public function get_fees();
}

interface OrderItemInterface {
  public function get_product();
  public function get_meta($key);
  public function update_meta_data($key, $value);
  public function add_meta_data($key, $value);
  public function save();
  public function get_id();
}

interface ProductInterface {
  public function is_type($type);
  public function get_id();
}

interface CartInterface {
  public function get_cart();
  public function add_fee($name, $amount, $taxable, $tax_class);
  public function fees_api();
  public function set_cart_contents($cart_contents);
}

interface CartFeesInterface {
  public function add_fee($args);
}

interface OrderItemFeeInterface {
  public function get_meta($key);
  public function add_meta_data($key, $value);
  public function get_amount();
  public function get_name();
  public function save();
}
