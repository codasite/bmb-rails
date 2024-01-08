<?php

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
}
