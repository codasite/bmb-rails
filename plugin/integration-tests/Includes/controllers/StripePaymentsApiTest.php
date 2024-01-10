<?php

use WStrategies\BMB\Includes\Controllers\StripePaymentsApi;
use WStrategies\BMB\Includes\Service\PaymentProcessors\StripeWebhookFunctions;
use WStrategies\BMB\Includes\Service\PaymentProcessors\StripeWebhookService;

require_once WPBB_PLUGIN_DIR . 'integration-tests/mock/StripeMock.php';

class StripePaymentsApiTest extends \WPBB_UnitTestCase {
  public function test_webhook_handler() {
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
    $api = new StripePaymentsApi([
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
  }
}
