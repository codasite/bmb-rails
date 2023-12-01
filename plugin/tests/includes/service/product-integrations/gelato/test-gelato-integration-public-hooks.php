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
