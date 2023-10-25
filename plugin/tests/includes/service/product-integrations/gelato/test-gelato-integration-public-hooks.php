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
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-bracket-config.php';

class GelatoIntegrationPublicHooksTest extends WPBB_UnitTestCase {
  public function test_play_marked_printed_when_payment_complete() {
    $bracket = self::factory()->bracket->create_and_get([
      'num_teams' => 4,
    ]);
    $play = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
      'is_printed' => false,
      'picks' => [
        new Wpbb_MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);
    $bracket_config = new Wpbb_BracketConfig(
      $play->id,
      'light',
      'top',
      'https://example.com'
    );
    // Create necessary mocks and stubs
    $wc_mock = $this->createMock(Wpbb_WcFunctions::class);
    $wc_order_item_stub = $this->createMock(OrderItemInterface::class);
    $wc_order_stub = $this->createMock(OrderInterface::class);
    $wc_product_stub = $this->createMock(ProductInterface::class);
    $integration_mock = $this->createMock(Wpbb_GelatoProductIntegration::class);
    $s3_mock = $this->createMock(Wpbb_S3Service::class);
    $utils_mock = $this->createMock(Wpbb_Utils::class);
    $product_utils_mock = $this->createMock(Wpbb_BracketProductUtils::class);

    $wc_order_stub->method('get_items')->willReturn([$wc_order_item_stub]);
    $wc_order_stub->method('get_id')->willReturn(99);
    $wc_order_item_stub->method('get_product')->willReturn($wc_product_stub);
    $wc_order_item_stub
      ->method('get_meta')
      ->willReturnCallback(function ($arg) use ($bracket_config) {
        if ($arg === 's3_url') {
          return 'sample-s3-url';
        }
        if ($arg === 'bracket_config') {
          return $bracket_config;
        }
        return null; // default return value, or you can throw an exception or whatever makes sense for your use case
      });
    $wc_order_item_stub->method('get_id')->willReturn(999);
    $product_utils_mock->method('is_bracket_product')->willReturn(true);
    $s3_mock->method('rename_from_url')->willReturn('sample-renamed-s3-url');
    $wc_mock->method('wc_get_order')->willReturn($wc_order_stub);

    $hooks = new Wpbb_GelatoPublicHooks($integration_mock, [
      'wc' => $wc_mock,
      's3' => $s3_mock,
      'utils' => $utils_mock,
      'bracket_product_utils' => $product_utils_mock,
    ]);

    $hooks->handle_payment_complete(1);
    $play = self::factory()->play->get_object_by_id($play->id);

    $this->assertTrue($play->is_printed);
  }
}
