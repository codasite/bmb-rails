<?php

use WStrategies\BMB\Includes\Domain\BracketConfig;
use WStrategies\BMB\Includes\Domain\MatchPick;
use WStrategies\BMB\Includes\Repository\BracketPlayRepo;
use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductUtils;
use WStrategies\BMB\Includes\Service\Http\BracketImageRequestFactory;
use WStrategies\BMB\Includes\Service\Http\HttpClientInterface;
use WStrategies\BMB\Includes\Service\ProductIntegrations\Gelato\GelatoProductIntegration;
use WStrategies\BMB\Includes\Service\ProductIntegrations\Gelato\GelatoPublicHooks;
use WStrategies\BMB\Includes\Service\ProductIntegrations\WcFunctions;
use WStrategies\BMB\Includes\Service\S3Service;
use WStrategies\BMB\Includes\Utils;
use WStrategies\BMB\Includes\Service\PdfService;

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
    $gelato_mock->method('play_exists')->willReturn(true);
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
  public function test_process_bracket_product_item_with_missing_front_design() {
    // Create a cart item mock
    $cart_item = ['variation_id' => 456];

    // Instantiate the class under test
    $hooks = new GelatoPublicHooks(
      $this->createMock(GelatoProductIntegration::class)
    );

    // Expect an exception
    $this->expectException(Exception::class);

    // Call the method
    $hooks->process_bracket_product_item($cart_item);
  }

  public function test_process_bracket_product_item_with_bracket_config() {
    $product_post = $this->create_post([
      'post_type' => 'product_variation',
    ]);
    // set the wpbb_font_design meta key
    update_post_meta($product_post->ID, 'wpbb_front_design', 'front-design');
    // Create a cart item mock with a bracket configuration
    $cart_item = [
      'variation_id' => $product_post->ID,
      'bracket_config' => new BracketConfig(
        1,
        2,
        'dark',
        'top',
        'https://example.com'
      ),
    ];

    // Mocking the handle_front_and_back_design method
    $hooks = $this->getMockBuilder(GelatoPublicHooks::class)
      ->setConstructorArgs([$this->createMock(GelatoProductIntegration::class)])
      ->onlyMethods(['handle_front_and_back_design'])
      ->getMock();

    $hooks
      ->method('handle_front_and_back_design')
      ->willReturn('s3-url-front-back');

    // Call the method
    $result = $hooks->process_bracket_product_item($cart_item);

    // Assert that the S3 URL is correctly set
    $this->assertEquals('s3-url-front-back', $result['s3_url']);
  }

  public function test_process_bracket_product_item_without_bracket_config() {
    $product_post = $this->create_post([
      'post_type' => 'product_variation',
    ]);
    // set the wpbb_font_design meta key
    update_post_meta($product_post->ID, 'wpbb_front_design', 'front-design');
    // Create a cart item mock without a bracket configuration
    $cart_item = [
      'variation_id' => $product_post->ID,
    ];

    // Mocking the handle_front_and_back_design method
    $hooks = $this->getMockBuilder(GelatoPublicHooks::class)
      ->setConstructorArgs([$this->createMock(GelatoProductIntegration::class)])
      ->onlyMethods(['handle_front_design_only'])
      ->getMock();

    $hooks->method('handle_front_design_only')->willReturn('s3-url-front-only');

    // Call the method
    $result = $hooks->process_bracket_product_item($cart_item);

    // Assert that the S3 URL is correctly set
    $this->assertEquals('s3-url-front-only', $result['s3_url']);
  }

  public function test_handle_front_design_only_success() {
    // Setup test data
    $front_url = 'http://example.com/front.pdf';
    $temp_filename = 'temp-uniqueid.pdf';
    $back_width = 12;
    $back_height = 16;
    if (!defined('BRACKET_BUILDER_S3_ORDER_BUCKET')) {
      define('BRACKET_BUILDER_S3_ORDER_BUCKET', 'test-bucket');
    }

    // Mock S3 and PDF service
    $s3_mock = $this->createMock(S3Service::class);
    $pdf_service_mock = $this->createMock(PdfService::class);

    // Mock methods
    $s3_mock
      ->method('get_from_url')
      ->with($front_url)
      ->willReturn('front_pdf_content');
    $pdf_service_mock->method('merge_pdfs')->willReturn('merged_pdf_content');
    $s3_mock
      ->method('put')
      ->with(
        BRACKET_BUILDER_S3_ORDER_BUCKET,
        $temp_filename,
        'merged_pdf_content'
      )
      ->willReturn('s3_upload_url');

    // Instantiate the class with mocked dependencies
    $hooks = new GelatoPublicHooks(
      $this->createMock(GelatoProductIntegration::class),
      [
        's3' => $s3_mock,
        'pdf_service' => $pdf_service_mock,
      ]
    );

    // Call the function
    $result = $hooks->handle_front_design_only(
      $front_url,
      $temp_filename,
      $back_width,
      $back_height
    );

    // Assert the expected result
    $this->assertEquals('s3_upload_url', $result);
  }

  public function test_handle_front_design_only_failure_in_getting_front() {
    // Setup test data
    $front_url = 'http://example.com/invalid_front.pdf';
    $temp_filename = 'temp-uniqueid.pdf';
    $back_width = 12;
    $back_height = 16;

    // Mock S3 and PDF service
    $s3_mock = $this->createMock(S3Service::class);
    $pdf_service_mock = $this->createMock(PDFService::class);

    // Mock methods to simulate failure in retrieving front design
    $s3_mock
      ->method('get_from_url')
      ->with($front_url)
      ->willReturn(null); // Simulate failure

    // Instantiate the class with mocked dependencies
    $hooks = new GelatoPublicHooks(
      $this->createMock(GelatoProductIntegration::class),
      [
        's3' => $s3_mock,
        'pdf_service' => $pdf_service_mock,
      ]
    );

    // Expect an exception
    $this->expectException(Exception::class);

    // Call the function
    $hooks->handle_front_design_only(
      $front_url,
      $temp_filename,
      $back_width,
      $back_height
    );
  }

  public function test_handle_front_design_only_failure_in_merge_or_upload() {
    // Setup test data
    $front_url = 'http://example.com/front.pdf';
    $temp_filename = 'temp-uniqueid.pdf';
    $back_width = 12;
    $back_height = 16;

    // Mock S3 and PDF service
    $s3_mock = $this->createMock(S3Service::class);
    $pdf_service_mock = $this->createMock(PdfService::class);

    // Mock methods for front retrieval and failure in PDF merge
    $s3_mock
      ->method('get_from_url')
      ->with($front_url)
      ->willReturn('front_pdf_content');
    $pdf_service_mock->method('merge_pdfs')->willReturn(false); // Simulate merge failure

    // Instantiate the class with mocked dependencies
    $hooks = new GelatoPublicHooks(
      $this->createMock(GelatoProductIntegration::class),
      [
        's3' => $s3_mock,
        'pdf_service' => $pdf_service_mock,
      ]
    );

    // Expect an exception
    $this->expectException(Exception::class);

    // Call the function
    $hooks->handle_front_design_only(
      $front_url,
      $temp_filename,
      $back_width,
      $back_height
    );
  }

  public function test_handle_front_and_back_design_success() {
    if (!defined('BRACKET_BUILDER_S3_ORDER_BUCKET')) {
      define('BRACKET_BUILDER_S3_ORDER_BUCKET', 'test-bucket');
    }
    // Setup test data
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
    ]);
    $front_url = 'http://example.com/front.pdf';
    $bracket_config = new BracketConfig(
      $play->id,
      $play->bracket_id,
      'dark',
      'top',
      'https://example.com'
    );
    $temp_filename = 'temp-uniqueid.pdf';

    // Mock necessary services
    $s3_mock = $this->createMock(S3Service::class);
    $pdf_service_mock = $this->createMock(PdfService::class);
    $client_mock = $this->createMock(HttpClientInterface::class);
    $request_factory_mock = $this->createMock(
      BracketImageRequestFactory::class
    );
    $gelato_mock = $this->createMock(GelatoProductIntegration::class);
    $play_repo_mock = $this->createMock(BracketPlayRepo::class);

    // Setup mocks for successful operations
    $s3_mock
      ->method('get_from_url')
      ->willReturnOnConsecutiveCalls('front_pdf_content', 'back_pdf_content');
    $pdf_service_mock->method('merge_pdfs')->willReturn('merged_pdf_content');
    $s3_mock
      ->method('put')
      ->with(
        BRACKET_BUILDER_S3_ORDER_BUCKET,
        $temp_filename,
        'merged_pdf_content'
      )
      ->willReturn('s3_upload_url');
    $play_repo_mock->method('get')->willReturn($play);
    $client_mock
      ->method('send_many')
      ->willReturn([['image_url' => 's3_upload_url']]);
    $gelato_mock->method('get_http_client')->willReturn($client_mock);
    $gelato_mock
      ->method('get_request_factory')
      ->willReturn($request_factory_mock);
    $gelato_mock->method('get_play_repo')->willReturn($play_repo_mock);

    // Instantiate the class with mocked dependencies
    $hooks = new GelatoPublicHooks($gelato_mock, [
      's3' => $s3_mock,
      'pdf_service' => $pdf_service_mock,
    ]);

    // Call the function
    $result = $hooks->handle_front_and_back_design(
      $front_url,
      $bracket_config,
      $temp_filename
    );

    // Assert the expected result
    $this->assertEquals('s3_upload_url', $result);
  }

  public function test_handle_front_and_back_design_failure_in_back_design() {
    if (!defined('BRACKET_BUILDER_S3_ORDER_BUCKET')) {
      define('BRACKET_BUILDER_S3_ORDER_BUCKET', 'test-bucket');
    }

    // Setup test data
    $front_url = 'http://example.com/front.pdf';
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
    ]);
    $bracket_config = new BracketConfig(
      $play->id,
      $play->bracket_id,
      'dark',
      'top',
      'https://example.com'
    );
    $temp_filename = 'temp-uniqueid.pdf';

    // Mock necessary services
    $s3_mock = $this->createMock(S3Service::class);
    $pdf_service_mock = $this->createMock(PdfService::class);
    $client_mock = $this->createMock(HttpClientInterface::class);
    $request_factory_mock = $this->createMock(
      BracketImageRequestFactory::class
    );
    $gelato_mock = $this->createMock(GelatoProductIntegration::class);
    $play_repo_mock = $this->createMock(BracketPlayRepo::class);

    // Setup mock for failure in getting back design
    $client_mock->method('send_many')->willReturn([]); // Simulate failure
    $play_repo_mock->method('get')->willReturn($play);
    $gelato_mock->method('get_http_client')->willReturn($client_mock);
    $gelato_mock
      ->method('get_request_factory')
      ->willReturn($request_factory_mock);
    $gelato_mock->method('get_play_repo')->willReturn($play_repo_mock);

    // Instantiate the class with mocked dependencies
    $hooks = new GelatoPublicHooks($gelato_mock, [
      's3' => $s3_mock,
      'pdf_service' => $pdf_service_mock,
    ]);

    // Expect an exception
    $this->expectException(Exception::class);

    // Call the function
    $hooks->handle_front_and_back_design(
      $front_url,
      $bracket_config,
      $temp_filename
    );
  }

  public function test_play_marked_printed_when_payment_complete() {
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    $play = $this->create_play([
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
    $play = $this->get_play($play->id);

    $this->assertTrue($play->is_printed);
  }

  public function test_anonymous_printed_play_linked_to_user() {
    $user = self::factory()->user->create_and_get([
      'user_email' => 'test@test.com',
    ]);
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    $play = $this->create_play([
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
    $play = $this->get_play($play->id);

    $this->assertEquals($user->ID, $play->author);
  }
}
