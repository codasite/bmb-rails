<?php

namespace WStrategies\BMB\Tests\Integration\Features\Notifications\Push;

use WStrategies\BMB\Features\Notifications\NotificationType;
use WStrategies\BMB\Features\Notifications\Push\FCMToken;
use WStrategies\BMB\Features\Notifications\Push\FCMTokenManager;
use WStrategies\BMB\Features\Notifications\Push\FCMTokenRepo;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class FCMTokenManagerTest extends WPBB_UnitTestCase {
  private FCMTokenManager $token_manager;
  private FCMTokenRepo $token_repo;
  private $user;

  public function setUp(): void {
    parent::setUp();
    $this->token_repo = new FCMTokenRepo();
    $this->token_manager = new FCMTokenManager([
      'token_repo' => $this->token_repo,
    ]);
    $this->user = $this->create_user();
  }

  public function test_get_target_tokens_returns_empty_for_no_tokens(): void {
    $tokens = $this->token_manager->get_target_tokens(
      NotificationType::TOURNAMENT_START,
      $this->user->ID
    );
    $this->assertEmpty($tokens);
  }

  public function test_get_target_tokens_returns_active_tokens(): void {
    // Add test tokens
    $token1 = new FCMToken([
      'user_id' => $this->user->ID,
      'device_id' => 'device1',
      'token' => 'token1',
      'device_type' => 'ios',
    ]);
    $token2 = new FCMToken([
      'user_id' => $this->user->ID,
      'device_id' => 'device2',
      'token' => 'token2',
      'device_type' => 'android',
    ]);
    $this->token_repo->add($token1);
    $this->token_repo->add($token2);

    $tokens = $this->token_manager->get_target_tokens(
      NotificationType::TOURNAMENT_START,
      $this->user->ID
    );

    $this->assertCount(2, $tokens);
    $this->assertContains('token1', $tokens);
    $this->assertContains('token2', $tokens);
  }

  public function test_get_target_tokens_excludes_inactive_tokens(): void {
    // Add test token
    $token = new FCMToken([
      'user_id' => $this->user->ID,
      'device_id' => 'device1',
      'token' => 'token1',
      'device_type' => 'ios',
    ]);
    $created = $this->token_repo->add($token);

    // Update last_used to be older than 30 days
    global $wpdb;
    $table = FCMTokenRepo::table_name();
    $wpdb->query(
      "UPDATE {$table} SET last_used_at = DATE_SUB(NOW(), INTERVAL 31 DAY)"
    );

    $tokens = $this->token_manager->get_target_tokens(
      NotificationType::TOURNAMENT_START,
      $this->user->ID
    );

    $this->assertEmpty($tokens);
  }

  public function test_handle_failed_delivery_removes_token(): void {
    // Add test token
    $token = new FCMToken([
      'user_id' => $this->user->ID,
      'device_id' => 'device1',
      'token' => 'token1',
      'device_type' => 'ios',
    ]);
    $this->token_repo->add($token);

    // Handle failed delivery
    $this->token_manager->handle_failed_delivery('token1');

    // Verify token was removed
    $result = $this->token_repo->get(['token' => 'token1', 'single' => true]);
    $this->assertNull($result);
  }

  public function test_cleanup_inactive_tokens(): void {
    // Add test tokens
    $token1 = new FCMToken([
      'user_id' => $this->user->ID,
      'device_id' => 'device1',
      'token' => 'token1',
      'device_type' => 'ios',
    ]);
    $token2 = new FCMToken([
      'user_id' => $this->user->ID,
      'device_id' => 'device2',
      'token' => 'token2',
      'device_type' => 'android',
    ]);
    $created1 = $this->token_repo->add($token1);
    $created2 = $this->token_repo->add($token2);

    // Make one token inactive
    global $wpdb;
    $table = FCMTokenRepo::table_name();
    $wpdb->query(
      "UPDATE {$table} SET last_used_at = DATE_SUB(NOW(), INTERVAL 31 DAY) 
       WHERE device_id = 'device1'"
    );

    // Run cleanup
    $removed = $this->token_manager->cleanup_inactive_tokens(30);

    $this->assertEquals(1, $removed);
    $this->assertNull(
      $this->token_repo->get(['device_id' => 'device1', 'single' => true])
    );
    $this->assertNotNull(
      $this->token_repo->get(['device_id' => 'device2', 'single' => true])
    );
  }
}
