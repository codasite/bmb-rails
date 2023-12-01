<?php
namespace WStrategies\BMB\Includes\Service\ProductIntegrations;

class WcFunctions {
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
  public function session_set(string $key, $value): void {
    WC()->session->set($key, $value);
  }
  public function session_get(string $key) {
    return WC()->session->get($key);
  }
  public function session_unset(string $key): void {
    WC()->session->__unset($key);
  }
}
