<?php

use WStrategies\BMB\Includes\Domain\BracketConfig;
use WStrategies\BMB\Includes\Domain\MatchPick;
use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductUtils;
use WStrategies\BMB\Includes\Service\ProductIntegrations\Gelato\GelatoProductIntegration;
use WStrategies\BMB\Includes\Service\ProductIntegrations\Gelato\GelatoPublicHooks;
use WStrategies\BMB\Includes\Service\ProductIntegrations\WcFunctions;
use WStrategies\BMB\Includes\Service\S3Service;
use WStrategies\BMB\Includes\Utils;

require_once WPBB_PLUGIN_DIR . 'tests/mock/WooCommerceMock.php';

class GelatoIntegrationPublicHooksTest extends WPBB_UnitTestCase {
  public function test_add_bracket_to_cart_item_data() {
    // Create necessary mocks and stubs
    $wc_mock = $this->createMock(WcFunctions::class);
    $gelato_mock = $this->createMock(GelatoProductIntegration::class);
    $product_stub = $this->createMock(ProductInterface::class);
    $bracket_product_utils_mock = $this->createMock(BracketProductUtils::class);

    // Configure the mocks
    $product_id = 123;
    $variation_id = 456;
    $bracket_id = 2;

    $bracket_config = new BracketConfig(
      1,
      $bracket_id,
      'dark',
      'top',
      'https://example.com'
    );

    $wc_mock->method('wc_get_product')->willReturn($product_stub);
    $bracket_product_utils_mock->method('is_bracket_product')->willReturn(true);
    $gelato_mock->method('has_bracket_config')->willReturn(true);
    $gelato_mock->method('get_bracket_config')->willReturn($bracket_config);

    $hooks = new GelatoPublicHooks($gelato_mock, [
      'wc' => $wc_mock,
      'bracket_product_utils' => $bracket_product_utils_mock,
    ]);

    // Call the method
    $cart_item_data = [];
    $result = $hooks->add_bracket_to_cart_item_data(
      $cart_item_data,
      $product_id,
      $variation_id
    );

    // Assertions
    $this->assertArrayHasKey('bracket_config', $result);
    $this->assertEquals($bracket_config, $result['bracket_config']);
    $this->assertEquals($bracket_id, $result['bracket_id']);
  }

  public function test_add_bracket_to_order_item() {
    // Create mocks for order item and order
    $order_item_stub = $this->createMock(OrderItemInterface::class);
    $order_stub = $this->createMock(OrderInterface::class);
    $gelato_mock = $this->createMock(GelatoProductIntegration::class);

    // Simulate cart item values with bracket configuration and S3 URL
    $values = [
      'bracket_config' => new BracketConfig(
        1,
        2,
        'dark',
        'top',
        'https://example.com'
      ),
      's3_url' => 'https://example-s3-url.com',
    ];

    // Expectations for the order item meta data additions
    $order_item_stub
      ->expects($this->exactly(6))
      ->method('add_meta_data')
      ->withConsecutive(
        ['bracket_config', $values['bracket_config']],
        ['bracket_theme', $values['bracket_config']->theme_mode],
        ['bracket_placement', $values['bracket_config']->bracket_placement],
        ['bracket_id', $values['bracket_config']->bracket_id],
        ['play_id', $values['bracket_config']->play_id],
        ['s3_url', $values['s3_url']]
      );

    // Instantiate your class (replace with your actual class name and constructor as needed)
    $hooks = new GelatoPublicHooks($gelato_mock);

    // Call the method
    $hooks->add_bracket_to_order_item(
      $order_item_stub,
      'dummy_cart_item_key',
      $values,
      $order_stub
    );
  }

  public function test_handle_before_checkout_process() {
    // Mocking WooCommerce Cart and its methods
    $cart_mock = $this->createMock(CartInterface::class);
    $wc_functions_mock = $this->createMock(WcFunctions::class);
    $wc_functions_mock
      ->method('WC')
      ->willReturn((object) ['cart' => $cart_mock]);

    // Simulate cart items with a bracket product and a regular product
    $bracket_product_mock = $this->createMock(ProductInterface::class);
    $regular_product_mock = $this->createMock(ProductInterface::class);
    $original_cart_items = [
      'bracket_item_key' => ['data' => $bracket_product_mock],
      'regular_item_key' => ['data' => $regular_product_mock],
    ];

    $cart_mock->method('get_cart')->willReturn($original_cart_items);

    // Setup bracket product utils mock
    $bracket_product_utils_mock = $this->createMock(BracketProductUtils::class);
    $bracket_product_utils_mock->method('is_bracket_product')->will(
      $this->returnCallback(function ($product) use ($bracket_product_mock) {
        return $product === $bracket_product_mock;
      })
    );

    $hooks = $this->getMockBuilder(GelatoPublicHooks::class)
      ->setConstructorArgs([
        $this->createMock(GelatoProductIntegration::class),
        [
          'wc' => $wc_functions_mock,
          'bracket_product_utils' => $bracket_product_utils_mock,
        ],
      ])
      ->onlyMethods(['process_bracket_product_item'])
      ->getMock();

    $hooks
      ->expects($this->once())
      ->method('process_bracket_product_item')
      ->with($this->equalTo($original_cart_items['bracket_item_key']))
      ->willReturn(['processed_bracket_item']);

    // Assert that cart contents are set correctly
    $expected_cart_items = [
      'bracket_item_key' => ['processed_bracket_item'],
      'regular_item_key' => $original_cart_items['regular_item_key'],
    ];

    $cart_mock
      ->expects($this->once())
      ->method('set_cart_contents')
      ->with($this->equalTo($expected_cart_items));

    // Call the method
    $hooks->handle_before_checkout_process();
  }

  public function test_play_marked_printed_when_payment_complete() {
    $bracket = self::factory()->bracket->create_and_get([
      'num_teams' => 4,
    ]);
    $play = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
      'is_printed' => false,
      'picks' => [
        new MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);
    $bracket_config = new BracketConfig(
      $play->id,
      $play->bracket_id,
      'light',
      'top',
      'https://example.com'
    );
    // Create necessary mocks and stubs
    $wc_mock = $this->createMock(WcFunctions::class);
    $wc_order_item_stub = $this->createMock(OrderItemInterface::class);
    $wc_order_stub = $this->createMock(OrderInterface::class);
    $wc_product_stub = $this->createMock(ProductInterface::class);
    $integration_mock = $this->createMock(GelatoProductIntegration::class);
    $s3_mock = $this->createMock(S3Service::class);
    $utils_mock = $this->createMock(Utils::class);
    $product_utils_mock = $this->createMock(BracketProductUtils::class);

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

    $hooks = new GelatoPublicHooks($integration_mock, [
      'wc' => $wc_mock,
      's3' => $s3_mock,
      'utils' => $utils_mock,
      'bracket_product_utils' => $product_utils_mock,
    ]);

    $hooks->handle_payment_complete(1);
    $play = self::factory()->play->get_object_by_id($play->id);

    $this->assertTrue($play->is_printed);
  }

  public function test_anonymous_printed_play_linked_to_user() {
    $user = self::factory()->user->create_and_get([
      'user_email' => 'test@test.com',
    ]);
    $bracket = self::factory()->bracket->create_and_get([
      'num_teams' => 4,
    ]);
    $play = self::factory()->play->create_and_get([
      'bracket_id' => $bracket->id,
      'author' => 0,
      'picks' => [
        new MatchPick([
          'round_index' => 0,
          'match_index' => 0,
          'winning_team_id' => $bracket->matches[0]->team1->id,
        ]),
      ],
    ]);
    $bracket_config = new BracketConfig(
      $play->id,
      $play->bracket_id,
      'light',
      'top',
      'https://example.com'
    );
    // Create necessary mocks and stubs
    $wc_mock = $this->createMock(WcFunctions::class);
    $wc_order_item_stub = $this->createMock(OrderItemInterface::class);
    $wc_order_stub = $this->createMock(OrderInterface::class);
    $wc_order_stub->method('get_user_id')->willReturn($user->ID);
    $wc_product_stub = $this->createMock(ProductInterface::class);
    $integration_mock = $this->createMock(GelatoProductIntegration::class);
    $s3_mock = $this->createMock(S3Service::class);
    $utils_mock = $this->createMock(Utils::class);
    $product_utils_mock = $this->createMock(BracketProductUtils::class);

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

    $hooks = new GelatoPublicHooks($integration_mock, [
      'wc' => $wc_mock,
      's3' => $s3_mock,
      'utils' => $utils_mock,
      'bracket_product_utils' => $product_utils_mock,
    ]);

    $hooks->handle_payment_complete($user->ID);
    $play = self::factory()->play->get_object_by_id($play->id);

    $this->assertEquals($user->ID, $play->author);
  }
}
