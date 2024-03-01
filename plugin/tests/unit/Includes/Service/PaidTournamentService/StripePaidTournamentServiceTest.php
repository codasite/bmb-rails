<?php

namespace WStrategies\BMB\tests\unit\Includes\Service\PaidTournamentService;
use Stripe\PaymentIntent;
use Stripe\Service\PaymentIntentService;
use Stripe\StripeClient;
use WP_Mock;
use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductUtils;
use WStrategies\BMB\Includes\Service\PaidTournamentService\StripeConnectedAccount;
use WStrategies\BMB\Includes\Service\PaidTournamentService\StripeConnectedAccountFactory;
use WStrategies\BMB\Includes\Service\PaidTournamentService\StripePaidTournamentService;

class StripePaidTournamentServiceTest extends TestCase {
  public function test_requires_payment_true() {
    $product_utils_mock = $this->getMockBuilder(BracketProductUtils::class)
      ->disableOriginalConstructor()
      ->getMock();
    $product_utils_mock->method('has_bracket_fee')->willReturn(true);
    $sot = new StripePaidTournamentService([
      'bracket_product_utils' => $product_utils_mock,
    ]);
    $play = new BracketPlay([
      'bracket_id' => 1,
    ]);
    $this->assertTrue($sot->requires_payment($play));
  }
  public function test_requires_payment_false() {
    $product_utils_mock = $this->getMockBuilder(BracketProductUtils::class)
      ->disableOriginalConstructor()
      ->getMock();
    $product_utils_mock->method('has_bracket_fee')->willReturn(false);
    $sot = new StripePaidTournamentService([
      'bracket_product_utils' => $product_utils_mock,
    ]);
    $play = new BracketPlay([
      'bracket_id' => 1,
    ]);
    $this->assertFalse($sot->requires_payment($play));
  }
  public function test_create_payment_intent_for_paid_tournament_play() {
    WP_Mock::userFunction('get_user_meta', [
      'return' => 'acct_1',
    ]);
    $stripe_mock = $this->getMockBuilder(StripeClient::class)
      ->disableOriginalConstructor()
      ->getMock();

    // Mock the PaymentIntentService
    $payment_intent_service_mock = $this->getMockBuilder(
      PaymentIntentService::class
    )
      ->disableOriginalConstructor()
      ->getMock();

    // Configure the PaymentIntentService mock
    $payment_intent_service_mock
      ->expects($this->once())
      ->method('create')
      ->with([
        'amount' => 100,
        'currency' => 'usd',
        'metadata' => [
          'bracket_id' => 1,
          'play_id' => 1,
        ],
      ])
      ->willReturn(
        $this->getMockBuilder(PaymentIntent::class)
          ->disableOriginalConstructor()
          ->getMock()
      );

    $connected_account_mock = $this->getMockBuilder(
      StripeConnectedAccount::class
    )
      ->disableOriginalConstructor()
      ->getMock();

    $connected_account_mock
      ->method('should_create_destination_charge')
      ->willReturn(false);

    $connected_account_factory_mock = $this->getMockBuilder(
      StripeConnectedAccountFactory::class
    )
      ->disableOriginalConstructor()
      ->getMock();

    $connected_account_factory_mock
      ->method('get_account')
      ->willReturn($connected_account_mock);

    // Set the PaymentIntentService mock as the paymentIntents property on the StripeClient mock
    $stripe_mock->paymentIntents = $payment_intent_service_mock;

    $product_utils_mock = $this->getMockBuilder(BracketProductUtils::class)
      ->disableOriginalConstructor()
      ->getMock();

    $product_utils_mock->method('get_bracket_fee')->willReturn(1.0);
    $sot = new StripePaidTournamentService([
      'stripe_client' => $stripe_mock,
      'bracket_product_utils' => $product_utils_mock,
      'connected_account_factory' => $connected_account_factory_mock,
    ]);
    $play = new BracketPlay([
      'id' => 1,
      'bracket_id' => 1,
      'bracket' => new Bracket([
        'id' => 1,
        'author' => 1,
      ]),
    ]);
    $intent = $sot->create_payment_intent_for_paid_tournament_play($play);
    $this->assertInstanceOf(PaymentIntent::class, $intent);
  }
  public function test_create_payment_intent_for_connected_account() {
    $stripe_mock = $this->getMockBuilder(StripeClient::class)
      ->disableOriginalConstructor()
      ->getMock();

    // Mock the PaymentIntentService
    $payment_intent_service_mock = $this->getMockBuilder(
      PaymentIntentService::class
    )
      ->disableOriginalConstructor()
      ->getMock();

    // Configure the PaymentIntentService mock
    $payment_intent_service_mock
      ->expects($this->once())
      ->method('create')
      ->with([
        'amount' => 100,
        'currency' => 'usd',
        'metadata' => [
          'bracket_id' => 1,
          'play_id' => 2,
        ],
        'application_fee_amount' => 100,
        'transfer_data' => [
          'destination' => 'acct_1',
        ],
      ])
      ->willReturn(
        $this->getMockBuilder(PaymentIntent::class)
          ->disableOriginalConstructor()
          ->getMock()
      );

    $connected_account_mock = $this->getMockBuilder(
      StripeConnectedAccount::class
    )
      ->disableOriginalConstructor()
      ->getMock();

    $connected_account_mock
      ->method('should_create_destination_charge')
      ->willReturn(true);

    $connected_account_mock->method('get_account_id')->willReturn('acct_1');

    // Set the PaymentIntentService mock as the paymentIntents property on the StripeClient mock
    $stripe_mock->paymentIntents = $payment_intent_service_mock;

    $product_utils_mock = $this->getMockBuilder(BracketProductUtils::class)
      ->disableOriginalConstructor()
      ->getMock();

    $product_utils_mock->method('get_bracket_fee')->willReturn(1.0);
    $sot = new StripePaidTournamentService([
      'stripe_client' => $stripe_mock,
      'bracket_product_utils' => $product_utils_mock,
      'connected_account_factory' => new class ([
        'connected_account_mock' => $connected_account_mock,
      ]) extends StripeConnectedAccountFactory {
        private StripeConnectedAccount $connected_account_mock;
        public function __construct(array $args = []) {
          parent::__construct($args);
          $this->connected_account_mock = $args['connected_account_mock'];
        }

        public function get_account(int $userId): StripeConnectedAccount {
          return $this->connected_account_mock;
        }
      },
    ]);
    $play = new BracketPlay([
      'id' => 2,
      'bracket_id' => 1,
      'bracket' => new Bracket([
        'id' => 1,
        'author' => 1,
      ]),
    ]);
    $intent = $sot->create_payment_intent_for_paid_tournament_play($play);
    $this->assertInstanceOf(PaymentIntent::class, $intent);
  }
  public function test_set_play_payment_intent_id() {
    WP_Mock::userFunction('update_post_meta', [
      'times' => 1,
      'args' => [
        1,
        StripePaidTournamentService::$PAYMENT_INTENT_ID_META_KEY,
        'test',
      ],
    ]);
    $sot = new StripePaidTournamentService();
    $sot->set_play_payment_intent_id(1, 'test');
    $this->assertConditionsMet();
  }

  public function test_filter_after_play_added_does_not_require_payment() {
    $playMock = $this->createMock(BracketPlay::class);
    $playMock->id = 123;

    $sot = $this->getMockBuilder(StripePaidTournamentService::class)
      ->onlyMethods([
        'requires_payment',
        'create_payment_intent_for_paid_tournament_play',
        'set_play_payment_intent_id',
      ])
      ->getMock();

    $sot->method('requires_payment')->willReturn(false);

    // Expect that other methods are not called
    $sot
      ->expects($this->never())
      ->method('create_payment_intent_for_paid_tournament_play');
    $sot->expects($this->never())->method('set_play_payment_intent_id');

    $sot->filter_after_play_added($playMock);
  }

  public function test_filter_after_play_added_requires_payment() {
    $playMock = $this->createMock(BracketPlay::class);
    $playMock->id = 123;
    $payment_intent_mock = $this->createMock(PaymentIntent::class);
    $payment_intent_mock
      ->method('__get')
      ->with('id')
      ->willReturn('test_id');

    $sot = $this->getMockBuilder(StripePaidTournamentService::class)
      ->onlyMethods([
        'should_create_payment_intent_for_play',
        'create_payment_intent_for_paid_tournament_play',
        'set_play_payment_intent_id',
      ])
      ->getMock();

    $sot->method('should_create_payment_intent_for_play')->willReturn(true);

    // Expect that other methods are called
    $sot
      ->expects($this->once())
      ->method('create_payment_intent_for_paid_tournament_play')
      ->with($playMock)
      ->willReturn($payment_intent_mock);
    $sot
      ->expects($this->once())
      ->method('set_play_payment_intent_id')
      ->with($playMock->id, 'test_id');

    $sot->filter_after_play_added($playMock);
  }

  public function test_filter_after_play_serialized() {
    $playMock = $this->createMock(BracketPlay::class);
    $playMock->id = 123;
    $payment_intent_mock = $this->createMock(PaymentIntent::class);
    $payment_intent_mock
      ->method('__get')
      ->willReturnMap([['id', 'test_id'], ['client_secret', 'test_secret']]);

    $sot = $this->getMockBuilder(StripePaidTournamentService::class)
      ->onlyMethods([
        'should_create_payment_intent_for_play',
        'create_payment_intent_for_paid_tournament_play',
        'set_play_payment_intent_id',
      ])
      ->getMock();

    $sot->method('should_create_payment_intent_for_play')->willReturn(true);

    // Expect that other methods are called
    $sot
      ->method('create_payment_intent_for_paid_tournament_play')
      ->with($playMock)
      ->willReturn($payment_intent_mock);

    $sot->filter_after_play_added($playMock);
    $data = $sot->filter_after_play_serialized([]);
    // assert that the client secret is added to the response data
    $this->assertSame(
      'test_secret',
      $data[StripePaidTournamentService::$CLIENT_SECRET_RESPONSE_DATA_KEY]
    );
    // assert that the id is added to the response data
    $this->assertSame(
      'test_id',
      $data[StripePaidTournamentService::$INTENT_ID_KEY]
    );
  }

  public function test_should_create_payment_intent_for_play_true_after_filter_request_params() {
    $product_utils_mock = $this->getMockBuilder(BracketProductUtils::class)
      ->disableOriginalConstructor()
      ->getMock();

    $product_utils_mock->method('has_bracket_fee')->willReturn(true);

    $playMock = $this->createMock(BracketPlay::class);
    $playMock->id = 123;
    $playMock->bracket_id = 1;

    $play_data = [
      StripePaidTournamentService::$SHOULD_CREATE_STRIPE_PAYMENT_INTENT_REQUEST_DATA_KEY => true,
    ];

    $sot = new StripePaidTournamentService([
      'bracket_product_utils' => $product_utils_mock,
    ]);
    $data = $sot->filter_request_params($play_data);

    $this->assertTrue($sot->should_create_payment_intent_for_play($playMock));
  }

  public function test_should_create_payment_intent_for_play_false_after_filter_request_params() {
    $product_utils_mock = $this->getMockBuilder(BracketProductUtils::class)
      ->disableOriginalConstructor()
      ->getMock();

    $product_utils_mock->method('has_bracket_fee')->willReturn(true);

    $playMock = $this->createMock(BracketPlay::class);
    $playMock->id = 123;
    $playMock->bracket_id = 1;

    $play_data = [];

    $sot = new StripePaidTournamentService([
      'bracket_product_utils' => $product_utils_mock,
    ]);
    $data = $sot->filter_request_params($play_data);

    $this->assertFalse($sot->should_create_payment_intent_for_play($playMock));
  }
}
