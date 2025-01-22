<?php

namespace WStrategies\BMB\Tests\Integration\Features\Notifications\Push;

use WStrategies\BMB\Features\Notifications\NotificationType;
use WStrategies\BMB\Features\Notifications\Push\FCMTokenManager;
use WStrategies\BMB\Features\Notifications\Push\FCMTokenRepo;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class FCMDeviceManagerTest extends WPBB_UnitTestCase {
  private FCMTokenManager $device_manager;
  private FCMTokenRepo $token_repo;

  public function setUp(): void {
    parent::setUp();
    $this->token_repo = new FCMTokenRepo();
    $this->device_manager = new FCMTokenManager([
      'token_repo' => $this->token_repo,
    ]);
  }

  public function test_get_target_devices_returns_empty_for_no_devices(): void {
    $tokens = $this->device_manager->get_target_device_tokens(
      NotificationType::TOURNAMENT_START,
      1
    );
    $this->assertEmpty($tokens);
  }

  public function test_get_target_devices_returns_active_devices(): void {
    // Add test devices
    $this->token_repo->add(1, 'device1', 'token1', 'ios');
    $this->token_repo->add(1, 'device2', 'token2', 'android');

    $tokens = $this->device_manager->get_target_device_tokens(
      NotificationType::TOURNAMENT_START,
      1
    );

    $this->assertCount(2, $tokens);
    $this->assertContains('token1', $tokens);
    $this->assertContains('token2', $tokens);
  }

  public function test_get_target_devices_excludes_inactive_devices(): void {
    // Add test devices
    $this->token_repo->add(1, 'device1', 'token1', 'ios');

    // Update last_used to be older than 30 days
    global $wpdb;
    $table = FCMTokenRepo::table_name();
    $wpdb->query(
      "UPDATE {$table} SET last_used_at = DATE_SUB(NOW(), INTERVAL 31 DAY)"
    );

    $tokens = $this->device_manager->get_target_device_tokens(
      NotificationType::TOURNAMENT_START,
      1
    );

    $this->assertEmpty($tokens);
  }

  public function test_handle_failed_delivery_removes_token(): void {
    // Add test device
    $this->token_repo->add(1, 'device1', 'token1', 'ios');

    // Handle failed delivery
    $this->device_manager->handle_failed_delivery('token1');

    // Verify token was removed
    $device = $this->token_repo->get(['token' => 'token1', 'single' => true]);
    $this->assertNull($device);
  }

  public function test_cleanup_inactive_tokens(): void {
    // Add test devices
    $this->token_repo->add(1, 'device1', 'token1', 'ios');
    $this->token_repo->add(1, 'device2', 'token2', 'android');

    // Make one device inactive
    global $wpdb;
    $table = FCMTokenRepo::table_name();
    $wpdb->query(
      "UPDATE {$table} SET last_used_at = DATE_SUB(NOW(), INTERVAL 31 DAY) 
       WHERE device_id = 'device1'"
    );

    // Run cleanup
    $removed = $this->device_manager->cleanup_inactive_tokens(30);

    $this->assertEquals(1, $removed);
    $this->assertNull(
      $this->token_repo->get(['device_id' => 'device1', 'single' => true])
    );
    $this->assertNotNull(
      $this->token_repo->get(['device_id' => 'device2', 'single' => true])
    );
  }

  public function test_update_app_version(): void {
    // Add test device
    $this->token_repo->add(1, 'device1', 'token1', 'ios');

    // Update app version
    $success = $this->device_manager->update_app_version(1, 'device1', '2.0.0');

    $this->assertTrue($success);
    $device = $this->token_repo->get([
      'device_id' => 'device1',
      'single' => true,
    ]);
    $this->assertEquals('2.0.0', $device['app_version']);
  }
}
