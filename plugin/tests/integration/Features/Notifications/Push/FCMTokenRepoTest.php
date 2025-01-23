<?php

namespace WStrategies\BMB\tests\integration\Features\Notifications\Push;

use WStrategies\BMB\Features\Notifications\Push\FCMToken;
use WStrategies\BMB\Features\Notifications\Push\FCMTokenRepo;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;
use WStrategies\BMB\Features\Notifications\Push\Exceptions\TokenDatabaseException;

class FCMTokenRepoTest extends WPBB_UnitTestCase {
  private FCMTokenRepo $repo;
  private $user;
  private $show_errors;

  public function set_up(): void {
    parent::set_up();
    $this->repo = new FCMTokenRepo();
    $this->user = $this->create_user();
    // Suppress errors during tests
    $this->show_errors = $this->repo->suppress_errors();
  }

  public function tear_down(): void {
    // Restore error display after each test
    if ($this->show_errors) {
      $this->repo->show_errors();
    }
    parent::tear_down();
  }

  public function test_add_token(): void {
    $token = new FCMToken([
      'user_id' => $this->user->ID,
      'device_id' => 'device123',
      'token' => 'fcm-token-123',
      'device_type' => 'ios',
      'device_name' => 'iPhone 12',
      'app_version' => '1.0.0',
    ]);

    $result = $this->repo->add($token);

    $this->assertNotNull($result);
    $this->assertEquals($this->user->ID, $result->user_id);
    $this->assertEquals('device123', $result->device_id);
    $this->assertEquals('fcm-token-123', $result->token);
    $this->assertEquals('ios', $result->device_type);
    $this->assertEquals('iPhone 12', $result->device_name);
    $this->assertEquals('1.0.0', $result->app_version);
  }

  public function test_get_token(): void {
    $token = new FCMToken([
      'user_id' => $this->user->ID,
      'device_id' => 'device123',
      'token' => 'fcm-token-123',
      'device_type' => 'ios',
    ]);
    $created = $this->repo->add($token);

    $result = $this->repo->get(['id' => $created->id, 'single' => true]);

    $this->assertNotNull($result);
    $this->assertEquals($created->id, $result->id);
    $this->assertEquals($this->user->ID, $result->user_id);
    $this->assertEquals('device123', $result->device_id);
  }

  public function test_update_token(): void {
    $token = new FCMToken([
      'user_id' => $this->user->ID,
      'device_id' => 'device123',
      'token' => 'old-token',
      'device_type' => 'ios',
    ]);
    $created = $this->repo->add($token);

    $updated = $this->repo->update_token($created->id, [
      'token' => 'new-token',
    ]);

    $this->assertNotNull($updated);
    $this->assertEquals($created->id, $updated->id);
    $this->assertEquals('new-token', $updated->token);
  }

  public function test_delete_token(): void {
    $token = new FCMToken([
      'user_id' => $this->user->ID,
      'device_id' => 'device123',
      'token' => 'fcm-token-123',
      'device_type' => 'ios',
    ]);
    $created = $this->repo->add($token);

    $deleted = $this->repo->delete($created->id);
    $result = $this->repo->get(['id' => $created->id, 'single' => true]);

    $this->assertTrue($deleted);
    $this->assertNull($result);
  }

  public function test_get_by_user(): void {
    // Add multiple devices for user
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
    $this->repo->add($token1);
    $this->repo->add($token2);

    $devices = $this->repo->get(['user_id' => $this->user->ID]);

    $this->assertCount(2, $devices);
    $this->assertEquals('device1', $devices[0]->device_id);
    $this->assertEquals('device2', $devices[1]->device_id);
  }

  public function test_update_device_info(): void {
    $token = new FCMToken([
      'user_id' => $this->user->ID,
      'device_id' => 'device123',
      'token' => 'fcm-token-123',
      'device_type' => 'ios',
      'device_name' => 'Old Phone',
      'app_version' => '1.0.0',
    ]);
    $created = $this->repo->add($token);

    $updated = $this->repo->update_token($created->id, [
      'device_name' => 'New Phone',
      'app_version' => '2.0.0',
    ]);

    $this->assertNotNull($updated);
    $this->assertEquals('New Phone', $updated->device_name);
    $this->assertEquals('2.0.0', $updated->app_version);
  }

  public function test_delete_inactive_tokens(): void {
    global $wpdb;

    // Add an old token
    $token = new FCMToken([
      'user_id' => $this->user->ID,
      'device_id' => 'device123',
      'token' => 'fcm-token-123',
      'device_type' => 'ios',
    ]);
    $created = $this->repo->add($token);

    // Manually update last_used_at to old date
    $table = FCMTokenRepo::table_name();
    $wpdb->update(
      $table,
      ['last_used_at' => '2020-01-01 00:00:00'],
      ['id' => $created->id]
    );

    // Add a recent token
    $token2 = new FCMToken([
      'user_id' => $this->user->ID,
      'device_id' => 'device456',
      'token' => 'fcm-token-456',
      'device_type' => 'ios',
    ]);
    $this->repo->add($token2);

    $deleted_count = $this->repo->delete_inactive_tokens(30);
    $remaining_tokens = $this->repo->get(['user_id' => $this->user->ID]);

    $this->assertEquals(1, $deleted_count);
    $this->assertCount(1, $remaining_tokens);
    $this->assertEquals('device456', $remaining_tokens[0]->device_id);
  }

  public function test_unique_device_per_user(): void {
    // First token for device
    $token1 = new FCMToken([
      'user_id' => $this->user->ID,
      'device_id' => 'device123',
      'token' => 'token1',
      'device_type' => 'ios',
    ]);
    $first = $this->repo->add($token1);

    // Second token for same device should throw
    $token2 = new FCMToken([
      'user_id' => $this->user->ID,
      'device_id' => 'device123',
      'token' => 'token2',
      'device_type' => 'android',
    ]);

    $this->expectException(TokenDatabaseException::class);
    $this->repo->add($token2);
  }

  public function test_unique_token_constraint(): void {
    // Create first token
    $token1 = new FCMToken([
      'user_id' => $this->user->ID,
      'device_id' => 'device1',
      'token' => 'unique-token',
      'device_type' => 'ios',
    ]);
    $this->repo->add($token1);

    // Attempt to use same token for different device
    $second_user = $this->create_user();
    $token2 = new FCMToken([
      'user_id' => $second_user->ID,
      'device_id' => 'device2',
      'token' => 'unique-token',
      'device_type' => 'ios',
    ]);

    $this->expectException(TokenDatabaseException::class);
    $this->repo->add($token2);
  }

  public function test_get_with_multiple_filters(): void {
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
    $this->repo->add($token1);
    $this->repo->add($token2);

    $result = $this->repo->get([
      'user_id' => $this->user->ID,
      'device_type' => 'ios',
      'single' => true,
    ]);

    $this->assertNotNull($result);
    $this->assertEquals('device1', $result->device_id);
    $this->assertEquals('ios', $result->device_type);
  }

  public function test_get_returns_null_when_no_matches(): void {
    $result = $this->repo->get(['user_id' => 999999, 'single' => true]);
    $this->assertNull($result);
  }

  public function test_get_returns_empty_array_when_no_matches(): void {
    $results = $this->repo->get(['user_id' => 999999]);
    $this->assertEmpty($results);
  }

  public function test_update_token_returns_null_for_invalid_id(): void {
    $result = $this->repo->update_token(999999, ['token' => 'new-token']);
    $this->assertNull($result);
  }

  public function test_delete_returns_true_for_nonexistent_id(): void {
    $result = $this->repo->delete(999999);
    $this->assertTrue($result);
  }

  public function test_multiple_devices_per_user(): void {
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
    $device1 = $this->repo->add($token1);
    $device2 = $this->repo->add($token2);

    $devices = $this->repo->get(['user_id' => $this->user->ID]);

    $this->assertCount(2, $devices);
    $this->assertNotEquals($device1->id, $device2->id);
    $this->assertEquals($device1->device_id, $devices[0]->device_id);
    $this->assertEquals($device2->device_id, $devices[1]->device_id);
  }

  public function test_cascade_delete_on_user_deletion(): void {
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
    $this->repo->add($token1);
    $this->repo->add($token2);

    wp_delete_user($this->user->ID);

    $remaining_tokens = $this->repo->get(['user_id' => $this->user->ID]);
    $this->assertEmpty($remaining_tokens);
  }

  public function test_delete_inactive_tokens_with_custom_threshold(): void {
    global $wpdb;

    // Add tokens with different dates
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
      'device_type' => 'ios',
    ]);
    $created1 = $this->repo->add($token1);
    $created2 = $this->repo->add($token2);

    $table = FCMTokenRepo::table_name();

    // Set one token to 5 days ago
    $wpdb->update(
      $table,
      ['last_used_at' => date('Y-m-d H:i:s', strtotime('-5 days'))],
      ['id' => $created1->id]
    );

    // Set another token to 15 days ago
    $wpdb->update(
      $table,
      ['last_used_at' => date('Y-m-d H:i:s', strtotime('-15 days'))],
      ['id' => $created2->id]
    );

    // Delete tokens older than 10 days
    $deleted_count = $this->repo->delete_inactive_tokens(10);

    $this->assertEquals(1, $deleted_count);

    $remaining = $this->repo->get(['user_id' => $this->user->ID]);
    $this->assertCount(1, $remaining);
    $this->assertEquals($created1->id, $remaining[0]->id);
  }
}
