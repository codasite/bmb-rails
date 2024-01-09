<?php

use Stripe\Service\PaymentIntentService;
use WStrategies\BMB\Includes\Controllers\StripePaymentsApi;
use WStrategies\BMB\Includes\Loader;
use WStrategies\BMB\Includes\Service\PaymentProcessors\StripePayments;
use WStrategies\BMB\tests\Includes\Service\PaymentProcessors\StripeMock;

require_once WPBB_PLUGIN_DIR . 'integration-tests/mock/StripeMock.php';

class StripePaymentsApiTest extends \WPBB_UnitTestCase {
  public function test_webhook_handler() {
    $request = new WP_REST_Request(
      'POST',
      '/wp-bracket-builder/v1/stripe/webhook'
    );
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(
      wp_json_encode([
        'foo' => 'bar',
      ])
    );
    $response = rest_do_request($request);
    $this->assertSame(200, $response->get_status());
    $this->assertSame('hello from webhook', $response->get_data());
  }

  public function test_create_payment_intent() {
    $bracket = $this->create_bracket([
      'id' => 1,
    ]);
    update_post_meta($bracket->id, 'bracket_fee', 1.0);
    $stripe_mock = $this->createMock(StripeMock::class);
    $stripe_mock->paymentIntents = $this->createMock(
      PaymentIntentService::class
    );
    $stripe_mock->paymentIntents
      ->expects($this->once())
      ->method('create')
      ->with([
        'amount' => 100,
        'currency' => 'usd',
        'metadata' => [
          'bracket_id' => 1,
        ],
      ])
      ->willReturn((object) ['client_secret' => 'test_secret']);
    $api = new StripePaymentsApi([
      'stripe_payments' => new StripePayments([
        'stripe_client' => $stripe_mock,
      ]),
    ]);
    $api->load(new Loader());
    $request = new WP_REST_Request(
      'POST',
      '/wp-bracket-builder/v1/stripe/create-payment-intent'
    );
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(
      wp_json_encode([
        'bracket_id' => 1,
      ])
    );
    $response = $api->create_payment_intent($request);
    $this->assertSame(
      ['client_secret' => 'test_secret'],
      $response->get_data()
    );
    $this->assertSame(200, $response->get_status());
  }
}
