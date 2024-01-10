<?php

use WStrategies\BMB\Includes\Controllers\StripeWebhooksApi;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Service\PaymentProcessors\StripeWebhookFunctions;
use WStrategies\BMB\Includes\Service\PaymentProcessors\StripeWebhookService;

require_once WPBB_PLUGIN_DIR . 'integration-tests/mock/StripeMock.php';

class StripeWebhooksApiTest extends \WPBB_UnitTestCase {
  public function test_webhook_handler_should_set_is_paid_to_true() {
    $this->create_bracket([
      'id' => 2,
    ]);
    $play = $this->create_play([
      'bracket_id' => 2,
      'id' => 123,
      'paid' => false,
    ]);
    $play_repo = new PlayRepo();
    $mock_stripe_webhook_functions = $this->createMock(
      StripeWebhookFunctions::class
    );
    $mock_payment_intent = $this->createMock(\Stripe\PaymentIntent::class);
    $mock_payment_intent
      ->method('__get')
      ->with('metadata')
      ->willReturn([
        'play_id' => 123,
      ]);
    $mock_stripe_webhook_functions
      ->expects($this->once())
      ->method('constructEvent')
      ->willReturn(
        (object) [
          'type' => 'payment_intent.succeeded',
          'data' => (object) [
            'object' => $mock_payment_intent,
          ],
        ]
      );
    $api = new StripeWebhooksApi([
      'webhook_service' => new StripeWebhookService([
        'stripe_webhook_functions' => $mock_stripe_webhook_functions,
      ]),
    ]);
    $request = new WP_REST_Request(
      'POST',
      '/wp-bracket-builder/v1/stripe/webhook'
    );
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('Stripe-Signature', 'foo');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(
      wp_json_encode([
        'foo' => 'bar',
      ])
    );
    $response = $api->handle_webhook($request);
    $this->assertSame('webhook success', $response->get_data());
    $this->assertSame(200, $response->get_status());
    $play = $play_repo->get(123);
    $this->assertTrue($play->is_paid);
  }

  public function test_webhook_handler_should_set_is_tournament_entry_to_true() {
    $this->create_bracket([
      'id' => 2,
    ]);
    $play = $this->create_play([
      'bracket_id' => 2,
      'id' => 123,
      'paid' => false,
    ]);
    $play_repo = new PlayRepo();
    $mock_stripe_webhook_functions = $this->createMock(
      StripeWebhookFunctions::class
    );
    $mock_payment_intent = $this->createMock(\Stripe\PaymentIntent::class);
    $mock_payment_intent
      ->method('__get')
      ->with('metadata')
      ->willReturn([
        'play_id' => 123,
      ]);
    $mock_stripe_webhook_functions
      ->expects($this->once())
      ->method('constructEvent')
      ->willReturn(
        (object) [
          'type' => 'payment_intent.succeeded',
          'data' => (object) [
            'object' => $mock_payment_intent,
          ],
        ]
      );
    $api = new StripeWebhooksApi([
      'webhook_service' => new StripeWebhookService([
        'stripe_webhook_functions' => $mock_stripe_webhook_functions,
      ]),
    ]);
    $request = new WP_REST_Request(
      'POST',
      '/wp-bracket-builder/v1/stripe/webhook'
    );
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('Stripe-Signature', 'foo');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(
      wp_json_encode([
        'foo' => 'bar',
      ])
    );
    $response = $api->handle_webhook($request);
    $this->assertSame('webhook success', $response->get_data());
    $this->assertSame(200, $response->get_status());
    $play = $play_repo->get(123);
    $this->assertTrue($play->is_tournament_entry);
  }
}
