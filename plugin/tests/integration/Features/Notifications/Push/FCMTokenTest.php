<?php

namespace WStrategies\BMB\tests\integration\Features\Notifications\Push;

use WStrategies\BMB\Features\Notifications\Push\FCMToken;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class FCMTokenTest extends WPBB_UnitTestCase {
  private $test_data;

  public function set_up(): void {
    parent::set_up();
    $this->test_data = [
      'id' => 123,
      'user_id' => 456,
      'device_id' => 'test-device-123',
      'token' => 'fcm-token-123',
      'device_type' => 'ios',
      'device_name' => 'iPhone 12',
      'app_version' => '1.0.0',
      'created_at' => '2024-01-01 00:00:00',
      'last_used_at' => '2024-01-01 00:00:00',
    ];
  }

  public function test_constructor_with_complete_data(): void {
    $token = new FCMToken($this->test_data);

    $this->assertEquals(123, $token->id);
    $this->assertEquals(456, $token->user_id);
    $this->assertEquals('test-device-123', $token->device_id);
    $this->assertEquals('fcm-token-123', $token->token);
    $this->assertEquals('ios', $token->device_type);
    $this->assertEquals('iPhone 12', $token->device_name);
    $this->assertEquals('1.0.0', $token->app_version);
    $this->assertEquals('2024-01-01 00:00:00', $token->created_at);
    $this->assertEquals('2024-01-01 00:00:00', $token->last_used_at);
  }

  public function test_constructor_with_minimal_data(): void {
    $minimal_data = [
      'user_id' => 456,
      'device_id' => 'test-device-123',
      'token' => 'fcm-token-123',
      'device_type' => 'android',
    ];

    $token = new FCMToken($minimal_data);

    $this->assertNull($token->id);
    $this->assertEquals(456, $token->user_id);
    $this->assertEquals('test-device-123', $token->device_id);
    $this->assertEquals('fcm-token-123', $token->token);
    $this->assertEquals('android', $token->device_type);
    $this->assertNull($token->device_name);
    $this->assertNull($token->app_version);
    $this->assertNotEmpty($token->created_at);
    $this->assertNotEmpty($token->last_used_at);
  }

  public function test_to_array(): void {
    $token = new FCMToken($this->test_data);
    $array = $token->to_array();

    $this->assertIsArray($array);
    $this->assertEquals($this->test_data, $array);
  }

  public function test_type_casting(): void {
    $data = [
      'id' => '123', // String instead of int
      'user_id' => '456', // String instead of int
      'device_id' => 'test-device-123',
      'token' => 'fcm-token-123',
      'device_type' => 'ios',
    ];

    $token = new FCMToken($data);

    $this->assertIsInt($token->id);
    $this->assertIsInt($token->user_id);
    $this->assertEquals(123, $token->id);
    $this->assertEquals(456, $token->user_id);
  }

  public function test_default_timestamps(): void {
    $minimal_data = [
      'user_id' => 456,
      'device_id' => 'test-device-123',
      'token' => 'fcm-token-123',
      'device_type' => 'ios',
    ];

    $before = current_time('mysql');
    $token = new FCMToken($minimal_data);
    $after = current_time('mysql');

    // Verify timestamps are between before and after
    $this->assertGreaterThanOrEqual($before, $token->created_at);
    $this->assertLessThanOrEqual($after, $token->created_at);
    $this->assertGreaterThanOrEqual($before, $token->last_used_at);
    $this->assertLessThanOrEqual($after, $token->last_used_at);
  }

  public function test_null_optional_fields(): void {
    $data = [
      'user_id' => 456,
      'device_id' => 'test-device-123',
      'token' => 'fcm-token-123',
      'device_type' => 'ios',
      'device_name' => null,
      'app_version' => null,
    ];

    $token = new FCMToken($data);

    $this->assertNull($token->device_name);
    $this->assertNull($token->app_version);
  }

  public function test_to_array_with_null_values(): void {
    $data = [
      'user_id' => 456,
      'device_id' => 'test-device-123',
      'token' => 'fcm-token-123',
      'device_type' => 'ios',
      'device_name' => null,
      'app_version' => null,
    ];

    $token = new FCMToken($data);
    $array = $token->to_array();

    $this->assertArrayHasKey('device_name', $array);
    $this->assertArrayHasKey('app_version', $array);
    $this->assertNull($array['device_name']);
    $this->assertNull($array['app_version']);
  }
}
