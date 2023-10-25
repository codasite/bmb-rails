<?php

class Wpbb_WcFunctions {
  public function wc_get_product(int $product_id) {
    return wc_get_product($product_id);
  }
  public function wc_add_notice(string $message, string $type = 'error'): void {
    wc_add_notice($message, $type);
  }
  public function WC() {
    return WC();
  }
  public function wc_get_order(int $order_id) {
    return wc_get_order($order_id);
  }
}
