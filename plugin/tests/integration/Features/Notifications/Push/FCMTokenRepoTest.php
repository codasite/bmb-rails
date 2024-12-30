<?php

namespace WStrategies\BMB\tests\integration\Features\Notifications\Push;

use WStrategies\BMB\Features\Notifications\Push\FCMTokenRepo;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class FCMTokenRepoTest extends WPBB_UnitTestCase {
  private FCMTokenRepo $repo;
  private $user;
  private $show_errors;

  public function set_up(): void {
    parent::set_up();
    $this->repo = new FCMTokenRepo();
    $this->user = $this->create_user();
    // Suppress errors during tests
    $this->show_errors = $this->repo->wpdb->suppress_errors();
  }

  public function tear_down(): void {
    // Restore error display after each test
    if ($this->show_errors) {
      $this->repo->wpdb->show_errors();
    }
    parent::tear_down();
  }

  public function test_add_token(): void {
    $token = $this->repo->add(
      $this->user->ID,
      'device123',
      'fcm-token-123',
      'ios',
      'iPhone 12',
      '1.0.0'
    );

    $this->assertNotNull($token);
    $this->assertEquals($this->user->ID, $token['user_id']);
    $this->assertEquals('device123', $token['device_id']);
    $this->assertEquals('fcm-token-123', $token['token']);
    $this->assertEquals('ios', $token['device_type']);
    $this->assertEquals('iPhone 12', $token['device_name']);
    $this->assertEquals('1.0.0', $token['app_version']);
  }

  public function test_get_token(): void {
    $created = $this->repo->add(
      $this->user->ID,
      'device123',
      'fcm-token-123',
      'ios'
    );

    $token = $this->repo->get(['id' => $created['id'], 'single' => true]);

    $this->assertNotNull($token);
    $this->assertEquals($created['id'], $token['id']);
    $this->assertEquals($this->user->ID, $token['user_id']);
    $this->assertEquals('device123', $token['device_id']);
  }

  public function test_update_token(): void {
    $created = $this->repo->add(
      $this->user->ID,
      'device123',
      'old-token',
      'ios'
    );

    $updated = $this->repo->update_token($created['id'], 'new-token');

    $this->assertNotNull($updated);
    $this->assertEquals($created['id'], $updated['id']);
    $this->assertEquals('new-token', $updated['token']);
  }

  public function test_delete_token(): void {
    $created = $this->repo->add(
      $this->user->ID,
      'device123',
      'fcm-token-123',
      'ios'
    );

    $deleted = $this->repo->delete($created['id']);
    $token = $this->repo->get(['id' => $created['id'], 'single' => true]);

    $this->assertTrue($deleted);
    $this->assertNull($token);
  }

  public function test_delete_by_device(): void {
    $created = $this->repo->add(
      $this->user->ID,
      'device123',
      'fcm-token-123',
      'ios'
    );

    $deleted = $this->repo->delete_by_device($this->user->ID, 'device123');
    $token = $this->repo->get(['id' => $created['id'], 'single' => true]);

    $this->assertTrue($deleted);
    $this->assertNull($token);
  }

  public function test_get_user_devices(): void {
    // Add multiple devices for user
    $this->repo->add($this->user->ID, 'device1', 'token1', 'ios');
    $this->repo->add($this->user->ID, 'device2', 'token2', 'android');

    $devices = $this->repo->get_user_devices($this->user->ID);

    $this->assertCount(2, $devices);
    $this->assertEquals('device1', $devices[0]['device_id']);
    $this->assertEquals('device2', $devices[1]['device_id']);
  }

  public function test_update_app_version(): void {
    $created = $this->repo->add(
      $this->user->ID,
      'device123',
      'fcm-token-123',
      'ios',
      null,
      '1.0.0'
    );

    $updated = $this->repo->update_app_version(
      $this->user->ID,
      'device123',
      '2.0.0'
    );
    $token = $this->repo->get(['id' => $created['id'], 'single' => true]);

    $this->assertTrue($updated);
    $this->assertEquals('2.0.0', $token['app_version']);
  }

  public function test_update_last_used(): void {
    $created = $this->repo->add(
      $this->user->ID,
      'device123',
      'fcm-token-123',
      'ios'
    );
    $original_last_used = $created['last_used_at'];

    // Wait 1 second to ensure timestamp changes
    sleep(1);

    $updated = $this->repo->update_last_used('fcm-token-123');
    $token = $this->repo->get(['id' => $created['id'], 'single' => true]);

    $this->assertTrue($updated);
    $this->assertNotEquals($original_last_used, $token['last_used_at']);
  }

  public function test_delete_inactive_tokens(): void {
    global $wpdb;

    // Add an old token by directly manipulating the last_used_at
    $token = $this->repo->add(
      $this->user->ID,
      'device123',
      'fcm-token-123',
      'ios'
    );
    $table = FCMTokenRepo::table_name();
    $wpdb->update(
      $table,
      ['last_used_at' => '2020-01-01 00:00:00'],
      ['id' => $token['id']]
    );

    // Add a recent token
    $this->repo->add($this->user->ID, 'device456', 'fcm-token-456', 'ios');

    $deleted_count = $this->repo->delete_inactive_tokens(30);
    $remaining_tokens = $this->repo->get_user_devices($this->user->ID);

    $this->assertEquals(1, $deleted_count);
    $this->assertCount(1, $remaining_tokens);
    $this->assertEquals('device456', $remaining_tokens[0]['device_id']);
  }

  public function test_unique_device_per_user(): void {
    // First token for device
    $first = $this->repo->add($this->user->ID, 'device123', 'token1', 'ios');

    // Second token for same device should update existing
    $second = $this->repo->add(
      $this->user->ID,
      'device123',
      'token2',
      'android'
    );

    $this->assertEquals($first['id'], $second['id']);
    $this->assertEquals('token2', $second['token']);
  }

  public function test_unique_token_constraint(): void {
    // Create first token
    $this->repo->add($this->user->ID, 'device1', 'unique-token', 'ios');

    // Attempt to use same token for different device
    $second_user = $this->create_user();
    $result = $this->repo->add(
      $second_user->ID,
      'device2',
      'unique-token',
      'ios'
    );

    // Check that the insert failed
    $this->assertNull($result);
  }

  public function test_get_with_multiple_filters(): void {
    // Test that get() works with multiple filter combinations
    $this->repo->add($this->user->ID, 'device1', 'token1', 'ios');
    $this->repo->add($this->user->ID, 'device2', 'token2', 'android');

    $result = $this->repo->get([
      'user_id' => $this->user->ID,
      'device_type' => 'ios',
      'single' => true,
    ]);

    $this->assertNotNull($result);
    $this->assertEquals('device1', $result['device_id']);
    $this->assertEquals('ios', $result['device_type']);
  }

  public function test_get_returns_empty_array_when_no_matches(): void {
    $results = $this->repo->get(['user_id' => 999999]);
    $this->assertIsArray($results);
    $this->assertEmpty($results);
  }

  public function test_update_token_returns_null_for_invalid_id(): void {
    $result = $this->repo->update_token(999999, 'new-token');
    $this->assertNull($result);
  }

  public function test_delete_returns_true_for_nonexistent_id(): void {
    // WordPress returns true even if no rows were affected
    $result = $this->repo->delete(999999);
    $this->assertTrue($result);
  }

  public function test_multiple_devices_per_user(): void {
    // Test that a user can have multiple devices
    $device1 = $this->repo->add($this->user->ID, 'device1', 'token1', 'ios');
    $device2 = $this->repo->add(
      $this->user->ID,
      'device2',
      'token2',
      'android'
    );

    $devices = $this->repo->get_user_devices($this->user->ID);

    $this->assertCount(2, $devices);
    $this->assertNotEquals($device1['id'], $device2['id']);
    $this->assertEquals($device1['device_id'], $devices[0]['device_id']);
    $this->assertEquals($device2['device_id'], $devices[1]['device_id']);
  }

  public function test_cascade_delete_on_user_deletion(): void {
    // Test that tokens are deleted when user is deleted
    $this->repo->add($this->user->ID, 'device1', 'token1', 'ios');
    $this->repo->add($this->user->ID, 'device2', 'token2', 'android');

    wp_delete_user($this->user->ID);

    $remaining_tokens = $this->repo->get_user_devices($this->user->ID);
    $this->assertEmpty($remaining_tokens);
  }

  public function test_update_app_version_with_nonexistent_device(): void {
    $result = $this->repo->update_app_version(
      $this->user->ID,
      'nonexistent_device',
      '1.0.0'
    );
    $this->assertFalse($result);
  }

  public function test_delete_inactive_tokens_with_custom_threshold(): void {
    global $wpdb;

    // Add tokens with different dates
    $token1 = $this->repo->add($this->user->ID, 'device1', 'token1', 'ios');
    $token2 = $this->repo->add($this->user->ID, 'device2', 'token2', 'ios');

    $table = FCMTokenRepo::table_name();

    // Set one token to 5 days ago
    $wpdb->update(
      $table,
      ['last_used_at' => date('Y-m-d H:i:s', strtotime('-5 days'))],
      ['id' => $token1['id']]
    );

    // Set another token to 15 days ago
    $wpdb->update(
      $table,
      ['last_used_at' => date('Y-m-d H:i:s', strtotime('-15 days'))],
      ['id' => $token2['id']]
    );

    // Delete tokens older than 10 days
    $deleted_count = $this->repo->delete_inactive_tokens(10);

    $this->assertEquals(1, $deleted_count);

    $remaining = $this->repo->get_user_devices($this->user->ID);
    $this->assertCount(1, $remaining);
    $this->assertEquals($token1['id'], $remaining[0]['id']);
  }

  public function test_update_app_version_scenarios(): void {
    // First test: Update nonexistent device should return false
    $result = $this->repo->update_app_version(
      $this->user->ID,
      'nonexistent_device',
      '1.0.0'
    );
    $this->assertFalse($result, 'Update should fail for nonexistent device');

    // Second test: Update existing device should return true
    $token = $this->repo->add(
      $this->user->ID,
      'device123',
      'fcm-token-123',
      'ios',
      null,
      '1.0.0'
    );

    $result = $this->repo->update_app_version(
      $this->user->ID,
      'device123',
      '2.0.0'
    );

    $this->assertTrue($result, 'Update should succeed for existing device');

    // Verify the update actually happened
    $updated = $this->repo->get(['id' => $token['id'], 'single' => true]);
    $this->assertEquals('2.0.0', $updated['app_version']);
  }
}
