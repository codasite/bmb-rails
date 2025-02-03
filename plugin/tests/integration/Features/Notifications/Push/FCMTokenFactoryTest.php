<?php

namespace WStrategies\BMB\tests\integration\Features\Notifications\Push;

use WStrategies\BMB\Features\Notifications\Push\FCMToken;
use WStrategies\BMB\Features\Notifications\Push\FCMTokenFactory;
use WStrategies\BMB\Includes\Domain\ValidationException;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class FCMTokenFactoryTest extends WPBB_UnitTestCase {
  private $valid_data;
  private $user;

  public function set_up(): void {
    parent::set_up();
    $this->user = $this->create_user();
    $this->valid_data = [
      'user_id' => $this->user->ID,
      'device_id' => 'test-device-123',
      'token' => 'fcm-token-123',
      'device_type' => 'ios',
      'device_name' => 'iPhone 12',
      'app_version' => '1.0.0',
    ];
  }

  public function test_create_with_valid_data(): void {
    $token = FCMTokenFactory::create($this->valid_data);

    $this->assertInstanceOf(FCMToken::class, $token);
    $this->assertEquals($this->user->ID, $token->user_id);
    $this->assertEquals('test-device-123', $token->device_id);
    $this->assertEquals('fcm-token-123', $token->token);
    $this->assertEquals('ios', $token->device_type);
  }

  public function test_create_with_minimal_data(): void {
    $minimal_data = [
      'user_id' => $this->user->ID,
      'device_id' => 'test-device-123',
      'token' => 'fcm-token-123',
      'device_type' => 'android',
    ];

    $token = FCMTokenFactory::create($minimal_data);

    $this->assertInstanceOf(FCMToken::class, $token);
    $this->assertNull($token->device_name);
    $this->assertNull($token->app_version);
  }

  public function test_validation_missing_required_fields(): void {
    $invalid_data_sets = [
      'missing user_id' => array_diff_key($this->valid_data, ['user_id' => '']),
      'missing device_id' => array_diff_key($this->valid_data, [
        'device_id' => '',
      ]),
      'missing token' => array_diff_key($this->valid_data, ['token' => '']),
      'missing device_type' => array_diff_key($this->valid_data, [
        'device_type' => '',
      ]),
    ];

    foreach ($invalid_data_sets as $case => $invalid_data) {
      try {
        FCMTokenFactory::create($invalid_data);
        $this->fail("ValidationException not thrown for $case");
      } catch (ValidationException $e) {
        $this->assertStringContainsString('required', $e->getMessage());
      }
    }
  }

  public function test_validation_invalid_user_id(): void {
    $this->valid_data['user_id'] = 999999; // Non-existent user ID

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('user_id for existing user is required');
    FCMTokenFactory::create($this->valid_data);
  }

  public function test_validation_invalid_device_type(): void {
    $this->valid_data['device_type'] = 'windows';

    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('device_type must be either ios or android');
    FCMTokenFactory::create($this->valid_data);
  }

  public function test_validation_field_lengths(): void {
    $long_string = str_repeat('a', 256);
    $invalid_data_sets = [
      'token too long' => ['token' => $long_string],
      'device_name too long' => ['device_name' => $long_string],
      'app_version too long' => ['app_version' => str_repeat('1', 51)],
    ];

    foreach ($invalid_data_sets as $case => $invalid_field) {
      $test_data = array_merge($this->valid_data, $invalid_field);
      try {
        FCMTokenFactory::create($test_data);
        $this->fail("ValidationException not thrown for $case");
      } catch (ValidationException $e) {
        $this->assertStringContainsString(
          'must be less than',
          $e->getMessage()
        );
      }
    }
  }

  public function test_validation_invalid_timestamps(): void {
    $invalid_timestamps = [
      'invalid created_at' => ['created_at' => 'not-a-date'],
      'invalid last_used_at' => ['last_used_at' => 'invalid-timestamp'],
    ];

    foreach ($invalid_timestamps as $case => $invalid_data) {
      $test_data = array_merge($this->valid_data, $invalid_data);
      try {
        FCMTokenFactory::create($test_data);
        $this->fail("ValidationException not thrown for $case");
      } catch (ValidationException $e) {
        $this->assertStringContainsString(
          'must be a valid datetime',
          $e->getMessage()
        );
      }
    }
  }

  public function test_validation_empty_strings(): void {
    $empty_fields = [
      'empty device_id' => ['device_id' => ''],
      'empty token' => ['token' => ''],
      'empty device_type' => ['device_type' => ''],
    ];

    foreach ($empty_fields as $case => $empty_field) {
      $test_data = array_merge($this->valid_data, $empty_field);
      try {
        FCMTokenFactory::create($test_data);
        $this->fail("ValidationException not thrown for $case");
      } catch (ValidationException $e) {
        $this->assertStringContainsString('required', $e->getMessage());
      }
    }
  }

  public function test_validation_whitespace_strings(): void {
    $whitespace_fields = [
      'whitespace device_id' => ['device_id' => '   '],
      'whitespace token' => ['token' => '  '],
      'whitespace device_type' => ['device_type' => ' '],
    ];

    foreach ($whitespace_fields as $case => $whitespace_field) {
      $test_data = array_merge($this->valid_data, $whitespace_field);
      try {
        FCMTokenFactory::create($test_data);
        $this->fail("ValidationException not thrown for $case");
      } catch (ValidationException $e) {
        $this->assertStringContainsString('required', $e->getMessage());
      }
    }
  }
}
