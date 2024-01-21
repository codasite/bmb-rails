<?php

use WStrategies\BMB\Includes\Domain\BracketConfig;
use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductHooks;
use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductUtils;
use WStrategies\BMB\Includes\Service\ProductIntegrations\WcFunctions;
use const WStrategies\BMB\Includes\Service\BracketProduct\BRACKET_FEE_META_PREFIX;

class BracketProductHooksTest extends WPBB_UnitTestCase {
  public function test_add_paid_bracket_fee_should_be_added() {
    $bracket = $this->create_bracket();
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
    ]);
    $bracket_id = $bracket->id;
    $play_id = $play->id;

    $cart_mock = $this->createMock(CartInterface::class);
    $cart_fees_mock = $this->createMock(CartFeesInterface::class);
    $wc_order_item_mock = $this->createMock(OrderItemInterface::class);
    $wc_order_item_mock
      ->method('get_meta')
      ->with('bracket_id')
      ->willReturn($bracket_id);
    $cart_mock->method('get_cart')->willReturn([
      'item1' => [
        'data' => $wc_order_item_mock,
        'product_id' => 1,
        'bracket_config' => new BracketConfig(
          $play_id,
          $bracket_id,
          'dark',
          'center',
          'url'
        ),
      ],
    ]);
    $cart_mock->method('fees_api')->willReturn($cart_fees_mock);
    $wc_mock = $this->createMock(WcFunctions::class);
    $bracket_product_utils_mock = $this->createMock(BracketProductUtils::class);
    $bracket_product_utils_mock->method('is_bracket_product')->willReturn(true);
    $bracket_product_utils_mock->method('get_bracket_fee')->willReturn(12.0);
    $bracket_product_utils_mock
      ->method('get_bracket_fee_name')
      ->willReturn('my bracket fee');
    $hooks = new BracketProductHooks([
      'bracket_product_utils' => $bracket_product_utils_mock,
      'wc' => $wc_mock,
    ]);
    $fee_mock = $this->createMock(OrderItemFeeInterface::class);
    $fee_id = 'my-bracket-fee';

    $cart_fees_mock
      ->expects($this->once())
      ->method('add_fee')
      ->with(
        $this->equalTo([
          'id' => $fee_id,
          'name' => 'my bracket fee',
          'amount' => 12.0,
          'taxable' => false,
          'tax_class' => '',
        ])
      )
      ->willReturn($fee_mock);
    $wc_mock
      ->expects($this->once())
      ->method('session_set')
      ->with(
        $this->equalTo(BRACKET_FEE_META_PREFIX . $bracket_id),
        $this->equalTo(['fee_amount' => 12.0])
      );

    $hooks->add_paid_bracket_fee_to_cart($cart_mock);
  }

  public function test_add_paid_bracket_fee_0_should_not_be_added() {
    $bracket = $this->create_bracket();
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
    ]);
    $bracket_id = $bracket->id;
    $play_id = $play->id;
    $cart_mock = $this->createMock(CartInterface::class);
    $cart_fees_mock = $this->createMock(CartFeesInterface::class);
    $wc_order_item_mock = $this->createMock(OrderItemInterface::class);
    $wc_order_item_mock
      ->method('get_meta')
      ->with('bracket_id')
      ->willReturn($bracket_id);
    $cart_mock->method('get_cart')->willReturn([
      'item1' => [
        'data' => $wc_order_item_mock,
        'product_id' => 1,
        'bracket_config' => new BracketConfig(
          $play_id,
          $bracket_id,
          'dark',
          'center',
          'url'
        ),
      ],
    ]);
    $wc_mock = $this->createMock(WcFunctions::class);
    $cart_mock->method('fees_api')->willReturn($cart_fees_mock);
    $bracket_product_utils_mock = $this->createMock(BracketProductUtils::class);
    $bracket_product_utils_mock->method('is_bracket_product')->willReturn(true);
    $bracket_product_utils_mock->method('get_bracket_fee')->willReturn(0.0);
    $hooks = new BracketProductHooks([
      'bracket_product_utils' => $bracket_product_utils_mock,
    ]);

    $cart_fees_mock->expects($this->never())->method('add_fee');
    $wc_mock->expects($this->never())->method('session_set');

    $hooks->add_paid_bracket_fee_to_cart($cart_mock);
  }

  public function test_add_fee_meta_to_order_item_matching_fee() {
    // Mocks and setup
    $item_mock = $this->createMock(OrderItemInterface::class);

    $bracket = $this->create_bracket();
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
    ]);
    $bracket_id = $bracket->id;
    $play_id = $play->id;

    $values_mock = [
      'bracket_config' => (object) [
        'bracket_id' => $bracket_id,
        'play_id' => $play_id,
      ],
    ];

    $bracket_utils_mock = $this->createMock(BracketProductUtils::class);
    $bracket_utils_mock->method('is_bracket_product')->willReturn(true);

    $wc_mock = $this->createMock(WcFunctions::class);
    $wc_mock
      ->method('session_get')
      ->with(BRACKET_FEE_META_PREFIX . $bracket_id)
      ->willReturn(['fee_amount' => 1.0]);

    $hooks = new BracketProductHooks([
      'bracket_product_utils' => $bracket_utils_mock,
      'wc' => $wc_mock,
    ]);

    // Expectation: add_meta_data should be called with the fee amount
    $item_mock
      ->expects($this->once())
      ->method('add_meta_data')
      ->with($this->equalTo('bracket_fee'), $this->equalTo(1.0));

    $wc_mock
      ->expects($this->once())
      ->method('session_unset')
      ->with($this->equalTo(BRACKET_FEE_META_PREFIX . $bracket_id));

    // Execute the method under test
    $hooks->add_fee_meta_to_order_item(
      $item_mock,
      'cart_item_key',
      $values_mock,
      null // Assuming $order is not used directly in your function
    );
  }

  public function test_add_fee_meta_to_order_item_no_matching_fee() {
    // Mocks and setup
    $item_mock = $this->createMock(OrderItemInterface::class);

    $bracket = $this->create_bracket();
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
    ]);
    $bracket_id = $bracket->id;
    $play_id = $play->id;

    $values_mock = [
      'bracket_config' => (object) [
        'bracket_id' => $bracket_id,
        'play_id' => $play_id,
      ],
    ];

    $bracket_utils_mock = $this->createMock(BracketProductUtils::class);
    $bracket_utils_mock->method('is_bracket_product')->willReturn(true);

    $wc_mock = $this->createMock(WcFunctions::class);
    $wc_mock
      ->method('session_get')
      ->with(BRACKET_FEE_META_PREFIX . $bracket_id)
      ->willReturn([]); // No session data

    $hooks = new BracketProductHooks([
      'bracket_product_utils' => $bracket_utils_mock,
      'wc' => $wc_mock,
    ]);

    // Expectation: add_meta_data should not be called as there is no matching fee session data
    $item_mock->expects($this->never())->method('add_meta_data');

    // Execute the method under test
    $hooks->add_fee_meta_to_order_item(
      $item_mock,
      'cart_item_key',
      $values_mock,
      null
    );
  }

  public function test_fee_is_not_added_to_paid_play() {
    $bracket = $this->create_bracket();
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_paid' => true,
    ]);
    $bracket_id = $bracket->id;
    $play_id = $play->id;

    $cart_mock = $this->createMock(CartInterface::class);
    $cart_fees_mock = $this->createMock(CartFeesInterface::class);
    $wc_order_item_mock = $this->createMock(OrderItemInterface::class);
    $wc_order_item_mock
      ->method('get_meta')
      ->with('bracket_id')
      ->willReturn($bracket_id);
    $cart_mock->method('get_cart')->willReturn([
      'item1' => [
        'data' => $wc_order_item_mock,
        'product_id' => 1,
        'bracket_config' => new BracketConfig(
          $play_id,
          $bracket_id,
          'dark',
          'center',
          'url'
        ),
      ],
    ]);
    $cart_mock->method('fees_api')->willReturn($cart_fees_mock);
    $wc_mock = $this->createMock(WcFunctions::class);
    $bracket_product_utils_mock = $this->createMock(BracketProductUtils::class);
    $bracket_product_utils_mock->method('is_bracket_product')->willReturn(true);
    $bracket_product_utils_mock->method('get_bracket_fee')->willReturn(12.0);
    $bracket_product_utils_mock
      ->method('get_bracket_fee_name')
      ->willReturn('my bracket fee');
    $hooks = new BracketProductHooks([
      'bracket_product_utils' => $bracket_product_utils_mock,
      'wc' => $wc_mock,
    ]);

    $cart_fees_mock->expects($this->never())->method('add_fee');
    $wc_mock->expects($this->never())->method('session_set');

    $hooks->add_paid_bracket_fee_to_cart($cart_mock);
  }
}
