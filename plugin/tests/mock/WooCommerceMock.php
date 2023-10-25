<?php
interface OrderInterface {
  public function get_items();
  public function get_id();
}

interface OrderItemInterface {
  public function get_product();
  public function get_meta($key);
  public function update_meta_data($key, $value);
  public function save();
  public function get_id();
  // ... Any other methods you need
}

interface ProductInterface {
  public function is_type($type);
  public function get_id();
  // ... Any other methods you need
}
