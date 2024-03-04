<?php
namespace WStrategies\BMB\tests\unit\Includes\Service\BracketProduct;
use CartFeesInterface;
use CartInterface;
use OrderItemFeeInterface;
use OrderItemInterface;
use WP_Mock;
use WP_Mock\Tools\TestCase;
use WP_Post;
use WStrategies\BMB\Includes\Domain\BracketConfig;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductHooks;
use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductUtils;
use WStrategies\BMB\Includes\Service\ProductIntegrations\WcFunctions;
use const WStrategies\BMB\Includes\Service\BracketProduct\BRACKET_FEE_META_PREFIX;

require_once WPBB_PLUGIN_DIR . 'tests/integration/mock/WooCommerceMock.php';

class BracketProductHooksTest extends TestCase {
  public function test_add_paid_bracket_fee_should_be_added() {
    $bracket_id = 1;
    $play_id = 2;
    WP_Mock::userFunction('current_user_can', [
      'times' => 1,
      'args' => ['wpbb_play_paid_bracket_for_free'],
      'return' => false,
    ]);
    WP_Mock::userFunction('sanitize_title', [
      'times' => 1,
      'args' => ['my bracket fee'],
      'return' => 'my-bracket-fee',
    ]);
    WP_Mock::userFunction('is_wp_error', [
      'return' => false,
    ]);
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
      'play_repo' => new class extends PlayRepo {
        public function get(
          int|WP_Post|null|Play $post = null,
          array $opts = []
        ): Play {
          return new Play([
            'bracket_id' => 1,
            'is_paid' => false,
          ]);
        }
      },
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
    $bracket_id = 1;
    $play_id = 2;
    WP_Mock::userFunction('current_user_can', [
      'times' => 1,
      'args' => ['wpbb_play_paid_bracket_for_free'],
      'return' => true,
    ]);
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
    $bracket_product_utils_mock->method('get_bracket_fee')->willReturn(12.0);
    $hooks = new BracketProductHooks([
      'bracket_product_utils' => $bracket_product_utils_mock,
      'play_repo' => new class extends PlayRepo {
        public function get(
          int|WP_Post|null|Play $post = null,
          array $opts = []
        ): Play {
          return new Play([
            'bracket_id' => 1,
            'is_paid' => false,
          ]);
        }
      },
    ]);

    $cart_fees_mock->expects($this->never())->method('add_fee');
    $wc_mock->expects($this->never())->method('session_set');

    $hooks->add_paid_bracket_fee_to_cart($cart_mock);
  }

  public function test_add_paid_bracket_fee_should_not_add_fee_when_user_has_play_paid_for_free_capability() {
    $bracket_id = 1;
    $play_id = 2;
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
      'play_repo' => new class extends PlayRepo {
        public function get(
          int|WP_Post|null|Play $post = null,
          array $opts = []
        ): Play {
          return new Play([
            'bracket_id' => 1,
            'is_paid' => false,
          ]);
        }
      },
    ]);

    $cart_fees_mock->expects($this->never())->method('add_fee');
    $wc_mock->expects($this->never())->method('session_set');

    $hooks->add_paid_bracket_fee_to_cart($cart_mock);
  }

  public function test_add_fee_meta_to_order_item_matching_fee() {
    // Mocks and setup
    $item_mock = $this->createMock(OrderItemInterface::class);

    $bracket_id = 1;
    $play_id = 2;

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
      'play_repo' => new class extends PlayRepo {
        public function get(
          int|WP_Post|null|Play $post = null,
          array $opts = []
        ): Play {
          return new Play([
            'bracket_id' => 1,
            'is_paid' => false,
          ]);
        }
      },
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

    $bracket_id = 1;
    $play_id = 2;

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
      'play_repo' => new class extends PlayRepo {
        public function get(
          int|WP_Post|null|Play $post = null,
          array $opts = []
        ): Play {
          return new Play([
            'bracket_id' => 1,
            'is_paid' => false,
          ]);
        }
      },
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
    $bracket_id = 1;
    $play_id = 2;

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
      'play_repo' => new class extends PlayRepo {
        public function get(
          int|WP_Post|null|Play $post = null,
          array $opts = []
        ): Play {
          return new Play([
            'bracket_id' => 1,
            'is_paid' => true,
          ]);
        }
      },
    ]);

    $cart_fees_mock->expects($this->never())->method('add_fee');
    $wc_mock->expects($this->never())->method('session_set');

    $hooks->add_paid_bracket_fee_to_cart($cart_mock);
  }
}
