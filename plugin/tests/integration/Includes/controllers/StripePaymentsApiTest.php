<?php
namespace WStrategies\BMB\tests\integration\Includes\controllers;


use WStrategies\BMB\Includes\Controllers\StripePaymentsApi;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Service\PaidTournamentService\StripeConnectedAccount;
use WStrategies\BMB\Includes\Service\PaidTournamentService\StripeConnectedAccountFactory;
use WStrategies\BMB\Includes\Service\PaidTournamentService\StripePaidTournamentService;
use WStrategies\BMB\Includes\Service\PaymentProcessors\StripeWebhookFunctions;
use WStrategies\BMB\Includes\Service\PaymentProcessors\StripeWebhookService;

require_once WPBB_PLUGIN_DIR . 'tests/integration/mock/StripeMock.php';

class StripePaymentsApiTest extends \WPBB_UnitTestCase {
  use SetupAdminUser;
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
    $play = $play_repo->get(123);
    $this->assertTrue($play->is_tournament_entry);
  }

  public function test_create_payment_intent_no_play_id() {
    $data = [];

    $request = new WP_REST_Request(
      'POST',
      '/wp-bracket-builder/v1/stripe/payment-intent'
    );
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(wp_json_encode($data));

    $res = rest_do_request($request);
    $this->assertSame(400, $res->get_status());
    $this->assertSame('play_id is required', $res->get_data());
  }

  public function test_create_payment_intent_play_not_found() {
    $data = [
      'play_id' => 124,
    ];

    $request = new WP_REST_Request(
      'POST',
      '/wp-bracket-builder/v1/stripe/payment-intent'
    );
    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(wp_json_encode($data));

    $res = rest_do_request($request);
    $this->assertSame('play not found', $res->get_data());
    $this->assertSame(404, $res->get_status());
  }

  public function test_create_payment_intent_play_exists() {
    $mock_payment_intent = $this->createMock(\Stripe\PaymentIntent::class);
    $mock_payment_intent
      ->method('__get')
      ->willReturnMap([
        ['client_secret', 'test_client_secret'],
        ['amount', 1000],
      ]);
    $bracket = $this->create_bracket();
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
    ]);
    $tournament_service_mock = $this->getMockBuilder(
      StripePaidTournamentService::class
    )
      ->disableOriginalConstructor()
      ->getMock();
    $tournament_service_mock
      ->expects($this->once())
      ->method('create_payment_intent_for_paid_tournament_play')
      ->with($play)
      ->willReturn($mock_payment_intent);

    $api = new StripePaymentsApi([
      'tournament_service' => $tournament_service_mock,
    ]);

    $data = [
      'play_id' => $play->id,
    ];

    $request = new WP_REST_Request(
      'POST',
      '/wp-bracket-builder/v1/stripe/payment-intent'
    );

    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(wp_json_encode($data));

    $res = $api->create_payment_intent($request);
    $this->assertEquals(
      [
        'client_secret' => 'test_client_secret',
        'amount' => 1000,
      ],
      $res->get_data()
    );
    $this->assertSame(200, $res->get_status());
  }

  public function test_author_can_create_payment_intent() {
    $mock_payment_intent = $this->createMock(\Stripe\PaymentIntent::class);
    $mock_payment_intent
      ->method('__get')
      ->willReturnMap([
        ['client_secret', 'test_client_secret'],
        ['amount', 1000],
      ]);
    $user = $this->create_user();
    $bracket = $this->create_bracket();
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'author' => $user->ID,
    ]);
    $tournament_service_mock = $this->getMockBuilder(
      StripePaidTournamentService::class
    )
      ->disableOriginalConstructor()
      ->getMock();
    $tournament_service_mock
      ->expects($this->once())
      ->method('create_payment_intent_for_paid_tournament_play')
      ->with($play)
      ->willReturn($mock_payment_intent);

    $api = new StripePaymentsApi([
      'tournament_service' => $tournament_service_mock,
    ]);

    $data = [
      'play_id' => $play->id,
    ];

    $request = new WP_REST_Request(
      'POST',
      '/wp-bracket-builder/v1/stripe/payment-intent'
    );

    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(wp_json_encode($data));

    wp_set_current_user($user->ID);

    $res = $api->create_payment_intent($request);

    $this->assertSame(200, $res->get_status());
    $this->assertEquals(
      [
        'client_secret' => 'test_client_secret',
        'amount' => 1000,
      ],
      $res->get_data()
    );
  }

  public function test_non_author_cannot_create_payment_intent() {
    $author = $this->create_user();
    $non_author = $this->create_user();
    $bracket = $this->create_bracket();
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'author' => $author->ID,
    ]);

    $data = [
      'play_id' => $play->id,
    ];

    $request = new WP_REST_Request(
      'POST',
      '/wp-bracket-builder/v1/stripe/payment-intent'
    );

    $request->set_header('Content-Type', 'application/json');
    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));
    $request->set_body(wp_json_encode($data));

    wp_set_current_user($non_author->ID);

    $res = rest_do_request($request);

    $this->assertSame(403, $res->get_status());
  }

  public function test_get_stripe_account() {
    $account_mock = $this->createMock(StripeConnectedAccount::class);
    $account_mock->method('get_stripe_account')->willReturn([
      'foo' => 'bar',
    ]);

    $account_factory = $this->createMock(StripeConnectedAccountFactory::class);
    $account_factory
      ->method('get_account_for_current_user')
      ->willReturn($account_mock);

    $api = new StripePaymentsApi([
      'connected_account_factory' => $account_factory,
    ]);

    $request = new WP_REST_Request(
      'GET',
      '/wp-bracket-builder/v1/stripe/account'
    );

    $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

    $res = $api->get_stripe_account($request);

    $this->assertSame(200, $res->get_status());
    $this->assertEquals(
      [
        'foo' => 'bar',
      ],
      $res->get_data()
    );
  }
  public function test_get_stripe_account_not_logged_in() {
    wp_set_current_user(0);

    $request = new WP_REST_Request(
      'GET',
      '/wp-bracket-builder/v1/stripe/account'
    );

    $res = rest_do_request($request);

    $this->assertSame(401, $res->get_status());
  }
}
