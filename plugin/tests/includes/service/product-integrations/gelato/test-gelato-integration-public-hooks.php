<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/product-integrations/gelato/class-wpbb-gelato-product-integration.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/product-integrations/class-wpbb-product-integration-interface.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/product-integrations/class-wpbb-wc-functions.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/object-storage/class-wpbb-s3-storage.php';
require_once WPBB_PLUGIN_DIR . 'includes/class-wpbb-utils.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/product-integrations/class-wpbb-wc-functions.php';
require_once WPBB_PLUGIN_DIR . 'tests/mock/WooCommerceMock.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/bracket-product/class-wpbb-bracket-product-utils.php';

class GelatoIntegrationPublicHooksTest extends WPBB_UnitTestCase {
  public function test_handle_payment_complete() {
    // Create necessary mocks and stubs
    $wc_mock = $this->createMock(Wpbb_WcFunctions::class);
    $wc_order_item_stub = $this->createMock(OrderItemInterface::class);
    $wc_order_stub = $this->createMock(OrderInterface::class);
    $wc_product_stub = $this->createMock(ProductInterface::class);
    $integration_mock = $this->createMock(Wpbb_GelatoProductIntegration::class);
    $s3_mock = $this->createMock(Wpbb_S3Service::class);
    $utils_mock = $this->createMock(Wpbb_Utils::class);
    $product_utils_mock = $this->createMock(Wpbb_BracketProductUtils::class);

    // Setup method returns
    $wc_order_stub->method('get_items')->willReturn([$wc_order_item_stub]);
    $wc_order_item_stub->method('get_product')->willReturn($wc_product_stub);
    $wc_order_item_stub->method('get_meta')->willReturn('sample-s3-url');
    $product_utils_mock->method('is_bracket_product')->willReturn(true);
    $integration_mock
      ->method('get_gelato_order_filename')
      ->willReturn('sample-order-filename');
    $s3_mock->method('rename_from_url')->willReturn('sample-renamed-s3-url');
    $wc_mock->method('wc_get_order')->willReturn($wc_order_stub);

    // Instantiate the class under test
    $hooks = new Wpbb_GelatoPublicHooks($integration_mock, [
      'wc' => $wc_mock,
      's3' => $s3_mock,
      'utils' => $utils_mock,
      'bracket_product_utils' => $product_utils_mock,
    ]);

    $hooks->handle_payment_complete(1);
  }
}
