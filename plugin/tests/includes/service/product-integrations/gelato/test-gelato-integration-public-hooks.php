<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/product-integrations/gelato/class-wpbb-gelato-product-integration.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/product-integrations/class-wpbb-product-integration-interface.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/product-integrations/class-wpbb-wc-functions.php';

class GelatoIntgrationPublicHooksTest extends WPBB_UnitTestCase {
  public function test_handle_payment_complete() {
    $wc_mock = $this->createMock(Wpbb_WcFunctions::class);
    $wc_order_item_stub = $this->createStub('WC_Order_Item');
    $wc_order_stub = $this->createStub('WC_Order');
    $wc_order_stub->method('get_items')->willReturn([
      [
        'product_id' => 1,
        'quantity' => 1,
      ],
    ]);
    $integration_mock = $this->createMock(Wpbb_GelatoProductIntegration::class);
    $hooks = new Wpbb_GelatoPublicHooks($integration_mock, [
      'wc' => $wc_mock,
    ]);

    $hooks->handle_payment_complete(1);
  }
}
