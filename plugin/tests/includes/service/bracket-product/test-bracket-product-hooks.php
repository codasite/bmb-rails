<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/bracket-product/class-wpbb-bracket-product-hooks.php';

class BracketProductHooksTest extends WPBB_UnitTestCase {
  public function test_add_paid_bracket_fee_should_be_added() {
    $bracket = self::factory()->bracket->create_and_get([
      'num_teams' => 4,
    ]);
    $cart_mock = $this->createMock(CartInterface::class);
    $wc_order_item_mock = $this->createMock(OrderItemInterface::class);
    $wc_order_item_mock->method('get_meta')->willReturn($bracket->id);
    $cart_mock->method('get_cart')->willReturn([
      'item1' => [
        'data' => $wc_order_item_mock,
        'product_id' => 1,
        'bracket_config' => new Wpbb_BracketConfig(
          1,
          $bracket->id,
          'dark',
          'center',
          'url'
        ),
      ],
    ]);
    $wc_mock = $this->createMock(Wpbb_WcFunctions::class);
    $bracket_product_utils_mock = $this->createMock(
      Wpbb_BracketProductUtils::class
    );
    $bracket_product_utils_mock->method('is_bracket_product')->willReturn(true);
    $bracket_product_utils_mock->method('get_bracket_fee')->willReturn(12.0);
    $bracket_product_utils_mock
      ->method('get_bracket_fee_name')
      ->willReturn('my bracket fee');
    $hooks = new Wpbb_BracketProductHooks([
      'bracket_product_utils' => $bracket_product_utils_mock,
    ]);

    $cart_mock
      ->expects($this->once())
      ->method('add_fee')
      ->with(
        $this->equalTo('my bracket fee'),
        $this->equalTo(12),
        $this->equalTo(false),
        $this->equalTo('')
      );

    $hooks->add_paid_bracket_fee_to_cart($cart_mock);
  }

  public function test_add_paid_bracket_fee_0_should_not_be_added() {
    $bracket = self::factory()->bracket->create_and_get([
      'num_teams' => 4,
    ]);
    $cart_mock = $this->createMock(CartInterface::class);
    $wc_order_item_mock = $this->createMock(OrderItemInterface::class);
    $wc_order_item_mock->method('get_meta')->willReturn($bracket->id);
    $cart_mock->method('get_cart')->willReturn([
      'item1' => [
        'data' => $wc_order_item_mock,
        'product_id' => 1,
        'bracket_config' => new Wpbb_BracketConfig(
          1,
          $bracket->id,
          'dark',
          'center',
          'url'
        ),
      ],
    ]);
    $wc_mock = $this->createMock(Wpbb_WcFunctions::class);
    $bracket_product_utils_mock = $this->createMock(
      Wpbb_BracketProductUtils::class
    );
    $bracket_product_utils_mock->method('is_bracket_product')->willReturn(true);
    $bracket_product_utils_mock->method('get_bracket_fee')->willReturn(0.0);
    $hooks = new Wpbb_BracketProductHooks([
      'bracket_product_utils' => $bracket_product_utils_mock,
    ]);

    $cart_mock->expects($this->never())->method('add_fee');

    $hooks->add_paid_bracket_fee_to_cart($cart_mock);
  }

  public function test_add_fee_meta_to_order_item_matching_fee() {
    $item_mock = $this->createMock(OrderItemInterface::class);
    $values_mock = [
      'bracket_id' => 1,
    ];
    $bracket_product_utils_mock = $this->createMock(
      Wpbb_BracketProductUtils::class
    );
    $bracket_fee_name = 'my bracket fee';
    $bracket_product_utils_mock->method('is_bracket_product')->willReturn(true);
    $bracket_product_utils_mock->method('get_bracket_fee')->willReturn(1.0);
    $bracket_product_utils_mock
      ->method('get_bracket_fee_name')
      ->willReturn($bracket_fee_name);

    $order_mock = $this->createMock(OrderInterface::class);
    $order_mock->method('get_fees')->willReturn([
      (object) [
        'name' => $bracket_fee_name,
        'total' => 1.0,
      ],
    ]);

    $hooks = new Wpbb_BracketProductHooks([
      'bracket_product_utils' => $bracket_product_utils_mock,
    ]);
    // expect $item_mock->add_meta_data() to be called with the following args
    $item_mock
      ->expects($this->once())
      ->method('add_meta_data')
      ->with($this->equalTo('bracket_fee'), $this->equalTo(1));

    $hooks->add_fee_meta_to_order_item(
      $item_mock,
      'cart_item_key',
      $values_mock,
      $order_mock
    );
  }

  public function test_add_fee_meta_to_order_item_no_matching_fee() {
    $item_mock = $this->createMock(OrderItemInterface::class);
    $values_mock = [
      'bracket_id' => 1,
    ];
    $bracket_product_utils_mock = $this->createMock(
      Wpbb_BracketProductUtils::class
    );
    $bracket_fee_name = 'my bracket fee';
    $bracket_product_utils_mock->method('is_bracket_product')->willReturn(true);
    $bracket_product_utils_mock->method('get_bracket_fee')->willReturn(1.0);
    $bracket_product_utils_mock
      ->method('get_bracket_fee_name')
      ->willReturn($bracket_fee_name);

    $order_mock = $this->createMock(OrderInterface::class);
    $order_mock->method('get_fees')->willReturn([
      (object) [
        'name' => 'some other fee',
        'total' => 1.0,
      ],
    ]);

    $hooks = new Wpbb_BracketProductHooks([
      'bracket_product_utils' => $bracket_product_utils_mock,
    ]);
    // expect $item_mock->add_meta_data() to be called with the following args
    $item_mock->expects($this->never())->method('add_meta_data');

    $hooks->add_fee_meta_to_order_item(
      $item_mock,
      'cart_item_key',
      $values_mock,
      $order_mock
    );
  }
}
