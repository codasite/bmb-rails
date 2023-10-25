<?php
require_once WPBB_PLUGIN_DIR .
  'includes/service/product-integrations/wc/class-wpbb-wc-interfaces.php';
class OrderWrapper implements OrderInterface {
  private $order;

  public function __construct(WC_Order $order) {
    $this->order = $order;
  }

  public function get_items() {
    return $this->order->get_items();
  }
  // ... Implement other methods
}

class OrderItemWrapper implements OrderItemInterface {
  private $item;

  public function __construct(WC_Order_Item $item) {
    $this->item = $item;
  }

  public function get_product() {
    return new ProductWrapper($this->item->get_product());
  }
  // ... Implement other methods
}

class ProductWrapper implements ProductInterface {
  private $product;

  public function __construct(WC_Product $product) {
    $this->product = $product;
  }

  public function is_type($type) {
    return $this->product->is_type($type);
  }
  // ... Implement other methods
}
