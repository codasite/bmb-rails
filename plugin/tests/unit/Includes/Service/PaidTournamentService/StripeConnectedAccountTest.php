<?php

namespace Includes\Service\PaidTournamentService;

use Stripe\Service\AccountLinkService;
use Stripe\Service\AccountService;
use WP_Mock;
use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Includes\Domain\Fakes\UserFake;
use WStrategies\BMB\Includes\Repository\Fakes\UserMetaRepoFake;
use WStrategies\BMB\Includes\Repository\Fakes\UserRepoFake;
use WStrategies\BMB\Includes\Service\PaidTournamentService\StripeConnectedAccount;
use WStrategies\BMB\tests\integration\mock\StripeAccountMock;
use WStrategies\BMB\tests\integration\mock\StripeMock;

class StripeConnectedAccountTest extends TestCase {
  private function create_user() {
    return new UserFake();
  }

  public function test_get_connected_account_id() {
    $user = $this->create_user();
    $meta_repo = new UserMetaRepoFake(
      StripeConnectedAccount::$CONNECTED_ACCOUNT_ID_META_KEY
    );
    $meta_repo->set($user->id, 'acct_1');
    $service = new StripeConnectedAccount([
      'user_id' => $user->id,
      'connected_account_id_meta_repo' => $meta_repo,
    ]);
    $acct_id = $service->get_account_id();
    $this->assertEquals('acct_1', $acct_id);
  }

  public function test_set_connected_account_id() {
    $meta_repo = new UserMetaRepoFake(
      StripeConnectedAccount::$CONNECTED_ACCOUNT_ID_META_KEY
    );
    $user = $this->create_user();
    $service = new StripeConnectedAccount([
      'user_id' => $user->id,
      'connected_account_id_meta_repo' => $meta_repo,
    ]);
    $service->set_account_id('acct_1');
    $acct_id = $meta_repo->get($user->id);
    $this->assertEquals('acct_1', $acct_id);
  }

  public function test_calculate_application_fee() {
    $user = $this->create_user();
    $service = new StripeConnectedAccount([
      'user_id' => $user->id,
    ]);
    $fee = $service->calculate_application_fee(2000);
    $this->assertEquals(140, $fee);
  }

  public function test_calculate_application_fee_minimum() {
    $user = $this->create_user();
    $service = new StripeConnectedAccount(['user_id' => $user->id]);
    $fee = $service->calculate_application_fee(100);
    $this->assertEquals(100, $fee);
  }

  public function test_create_connected_account() {
    $user = $this->create_user();
    $stripe_mock = $this->getMockBuilder(StripeMock::class)
      ->disableOriginalConstructor()
      ->getMock();

    $stripe_accounts_mock = $this->getMockBuilder(AccountService::class)
      ->disableOriginalConstructor()
      ->getMock();

    $stripe_accounts_mock
      ->expects($this->once())
      ->method('create')
      ->with([
        'type' => 'express',
        'email' => $user->user_email,
      ])
      ->willReturn((object) ['id' => 'acct_1']);

    $stripe_mock->accounts = $stripe_accounts_mock;

    $service = new StripeConnectedAccount([
      'user_id' => $user->id,
      'stripe_client' => $stripe_mock,
      'connected_account_id_meta_repo' => new UserMetaRepoFake(
        StripeConnectedAccount::$CONNECTED_ACCOUNT_ID_META_KEY
      ),
      'user_repo' => new UserRepoFake($user),
    ]);
    $acct_id = $service->get_or_create_account_id();
    $this->assertEquals('acct_1', $acct_id);
  }

  public function test_get_onboarding_link() {
    WP_Mock::userFunction('get_permalink', [
      'return' => 'http://example.com',
    ]);
    WP_Mock::userFunction('get_page_by_path', [
      'return' => 'http://example.com',
    ]);
    $user = $this->create_user();
    $stripe_mock = $this->createMock(StripeMock::class);

    $stripe_accounts_mock = $this->getMockBuilder(AccountService::class)
      ->disableOriginalConstructor()
      ->getMock();

    $stripe_accounts_mock
      ->method('create')
      ->willReturn((object) ['id' => 'acct_1']);

    $links_mock = $this->getMockBuilder(AccountLinkService::class)
      ->disableOriginalConstructor()
      ->getMock();

    $links_mock
      ->expects($this->once())
      ->method('create')
      ->with([
        'account' => 'acct_1',
        'refresh_url' => 'http://example.com',
        'return_url' => 'http://example.com',
        'type' => 'account_onboarding',
      ])
      ->willReturn((object) ['url' => 'http://example.com']);

    $stripe_mock->accountLinks = $links_mock;
    $stripe_mock->accounts = $stripe_accounts_mock;

    $account = new StripeConnectedAccount([
      'user_id' => $user->id,
      'stripe_client' => $stripe_mock,
      'connected_account_id_meta_repo' => new UserMetaRepoFake(
        StripeConnectedAccount::$CONNECTED_ACCOUNT_ID_META_KEY
      ),
      'user_repo' => new UserRepoFake($user),
    ]);
    $link = $account->get_onboarding_link();
    $this->assertEquals('http://example.com', $link);
  }

  public function test_create_or_get_account_id_existing_account() {
    $user = $this->create_user();
    $meta_repo = new UserMetaRepoFake(
      StripeConnectedAccount::$CONNECTED_ACCOUNT_ID_META_KEY
    );
    $meta_repo->set($user->id, 'acct_1');

    $stripe_mock = $this->createMock(StripeMock::class);
    $stripe_mock->accounts = $this->getMockBuilder(AccountService::class)
      ->disableOriginalConstructor()
      ->getMock();
    $account_mock = $this->createMock(StripeAccountMock::class);
    $stripe_mock->accounts
      ->expects($this->once())
      ->method('retrieve')
      ->willReturn($account_mock);
    $stripe_mock->accounts->expects($this->never())->method('create');
    $account = new StripeConnectedAccount([
      'user_id' => $user->id,
      'stripe_client' => $stripe_mock,
      'connected_account_id_meta_repo' => $meta_repo,
      'user_repo' => new UserRepoFake($user),
    ]);
    $acct_id = $account->get_or_create_account_id();
    $this->assertEquals('acct_1', $acct_id);
  }

  public function test_create_or_get_account_id_new_id() {
    $user = $this->create_user();
    $stripe_mock = $this->createMock(StripeMock::class);
    $stripe_accounts_mock = $this->getMockBuilder(AccountService::class)
      ->disableOriginalConstructor()
      ->getMock();

    $stripe_accounts_mock
      ->method('create')
      ->willReturn((object) ['id' => 'acct_1']);

    $stripe_mock->accounts = $stripe_accounts_mock;

    $service = new StripeConnectedAccount([
      'user_id' => $user->id,
      'stripe_client' => $stripe_mock,
      'connected_account_id_meta_repo' => new UserMetaRepoFake(
        StripeConnectedAccount::$CONNECTED_ACCOUNT_ID_META_KEY
      ),
      'user_repo' => new UserRepoFake($user),
    ]);
    $acct_id = $service->get_or_create_account_id();
    $this->assertEquals('acct_1', $acct_id);
  }

  public function test_charges_enabled_true() {
    $user = $this->create_user();

    $stripe_account_mock = $this->createMock(StripeAccountMock::class);
    $stripe_account_mock->charges_enabled = true;

    /** @var \PHPUnit\Framework\MockObject\MockObject|StripeConnectedAccount $account */
    $account = $this->getMockBuilder(StripeConnectedAccount::class)
      ->setConstructorArgs([
        'args' => [
          'user_id' => $user->id,
        ],
      ])
      ->onlyMethods(['get_stripe_account'])
      ->getMock();

    $account->method('get_stripe_account')->willReturn($stripe_account_mock);

    $this->assertTrue($account->charges_enabled());
  }

  public function test_charges_enabled_false() {
    $user = $this->create_user();

    $stripe_account_mock = $this->createMock(StripeAccountMock::class);
    $stripe_account_mock->charges_enabled = false;

    /** @var \PHPUnit\Framework\MockObject\MockObject|StripeConnectedAccount $account */
    $account = $this->getMockBuilder(StripeConnectedAccount::class)
      ->setConstructorArgs([
        'args' => [
          'user_id' => $user->id,
        ],
      ])
      ->onlyMethods(['get_stripe_account'])
      ->getMock();

    $account->method('get_stripe_account')->willReturn($stripe_account_mock);

    $this->assertFalse($account->charges_enabled());
  }

  public function test_charges_enabled_no_account() {
    $user = $this->create_user();
    $account = new StripeConnectedAccount([
      'user_id' => $user->id,
      'connected_account_id_meta_repo' => new UserMetaRepoFake(
        StripeConnectedAccount::$CONNECTED_ACCOUNT_ID_META_KEY
      ),
    ]);
    $this->assertFalse($account->charges_enabled());
  }

  public function test_get_onboarding_or_login_link_charges_enabled() {
    $user = $this->create_user();
    $meta_repo = new UserMetaRepoFake(
      StripeConnectedAccount::$CONNECTED_ACCOUNT_ID_META_KEY
    );
    $meta_repo->set($user->id, 'acct_1');
    $stripe_mock = $this->createMock(StripeMock::class);
    $stripe_accounts_mock = $this->getMockBuilder(AccountService::class)
      ->disableOriginalConstructor()
      ->getMock();
    $stripe_account_mock = $this->createMock(StripeAccountMock::class);
    $stripe_account_mock->charges_enabled = true;
    $stripe_accounts_mock
      ->expects($this->once())
      ->method('retrieve')
      ->willReturn($stripe_account_mock);
    $stripe_accounts_mock->expects($this->never())->method('create');
    $stripe_accounts_mock
      ->expects($this->once())
      ->method('createLoginLink')
      ->with('acct_1')
      ->willReturn((object) ['url' => 'http://example.com']);

    $stripe_mock->accounts = $stripe_accounts_mock;

    $account = new StripeConnectedAccount([
      'user_id' => $user->id,
      'stripe_client' => $stripe_mock,
      'connected_account_id_meta_repo' => $meta_repo,
      'user_repo' => new UserRepoFake($user),
    ]);

    $link = $account->get_onboarding_or_login_link();
    $this->assertEquals('http://example.com', $link);
  }

  public function test_get_onboarding_or_login_link_new_acct() {
    WP_Mock::userFunction('get_permalink', [
      'return' => '',
    ]);
    WP_Mock::userFunction('get_page_by_path', [
      'return' => '',
    ]);

    $user = $this->create_user();
    $stripe_mock = $this->createMock(StripeMock::class);
    $stripe_accounts_mock = $this->getMockBuilder(AccountService::class)
      ->disableOriginalConstructor()
      ->getMock();

    $stripe_accounts_mock
      ->method('create')
      ->willReturn((object) ['id' => 'acct_1']);

    $links_mock = $this->getMockBuilder(AccountLinkService::class)
      ->disableOriginalConstructor()
      ->getMock();

    $links_mock
      ->expects($this->once())
      ->method('create')
      ->with([
        'account' => 'acct_1',
        'refresh_url' => '',
        'return_url' => '',
        'type' => 'account_onboarding',
      ])
      ->willReturn((object) ['url' => 'http://example.com']);

    $stripe_mock->accountLinks = $links_mock;
    $stripe_mock->accounts = $stripe_accounts_mock;

    $account = new StripeConnectedAccount([
      'user_id' => $user->id,
      'stripe_client' => $stripe_mock,
      'connected_account_id_meta_repo' => new UserMetaRepoFake(
        StripeConnectedAccount::$CONNECTED_ACCOUNT_ID_META_KEY
      ),
      'user_repo' => new UserRepoFake($user),
    ]);
    $link = $account->get_onboarding_or_login_link();
    $this->assertEquals('http://example.com', $link);
  }

  public function test_get_onboarding_or_login_charges_disabled() {
    $user = $this->create_user();

    /** @var \PHPUnit\Framework\MockObject\MockObject|StripeConnectedAccount $account */
    $account = $this->getMockBuilder(StripeConnectedAccount::class)
      ->setConstructorArgs([
        'args' => [
          'user_id' => $user->id,
        ],
      ])
      ->onlyMethods(['charges_enabled', 'get_onboarding_link'])
      ->getMock();

    $account->method('charges_enabled')->willReturn(false);
    $account->method('get_onboarding_link')->willReturn('http://example.com');

    $link = $account->get_onboarding_or_login_link();
    $this->assertEquals('http://example.com', $link);
  }
}
