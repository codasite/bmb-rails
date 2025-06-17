<?php

namespace WStrategies\BMB\Tests\Integration\Includes\Service;

use WStrategies\BMB\Includes\Service\SettingsService;
use WP_UnitTestCase;

/**
 * Test class for SettingsService
 */
class SettingsServiceTest extends WP_UnitTestCase {
  private SettingsService $settings_service;

  protected function setUp(): void {
    parent::setUp();
    $this->settings_service = new SettingsService();
  }

  protected function tearDown(): void {
    delete_option('wpbb_settings');
    parent::tearDown();
  }

  public function test_get_featured_brackets_count_returns_default_when_not_set(): void {
    $this->assertEquals(
      15,
      $this->settings_service->get_featured_brackets_count()
    );
  }

  public function test_get_featured_brackets_count_returns_set_value(): void {
    $this->settings_service->update_featured_brackets_count(25);
    $this->assertEquals(
      25,
      $this->settings_service->get_featured_brackets_count()
    );
  }

  public function test_update_featured_brackets_count_clamps_to_minimum(): void {
    $this->settings_service->update_featured_brackets_count(0);
    $this->assertEquals(
      1,
      $this->settings_service->get_featured_brackets_count()
    );
  }

  public function test_update_featured_brackets_count_clamps_to_maximum(): void {
    $this->settings_service->update_featured_brackets_count(100);
    $this->assertEquals(
      50,
      $this->settings_service->get_featured_brackets_count()
    );
  }

  public function test_get_setting_returns_default_when_not_set(): void {
    $this->assertNull($this->settings_service->get_setting('nonexistent_key'));
    $this->assertEquals(
      'default',
      $this->settings_service->get_setting('nonexistent_key', 'default')
    );
  }

  public function test_update_setting_and_get_setting_work_together(): void {
    $this->settings_service->update_setting('test_key', 'test_value');
    $this->assertEquals(
      'test_value',
      $this->settings_service->get_setting('test_key')
    );
  }

  public function test_get_all_settings_returns_empty_array_when_no_settings(): void {
    $this->assertEquals([], $this->settings_service->get_all_settings());
  }

  public function test_get_all_settings_returns_all_settings(): void {
    $this->settings_service->update_setting('key1', 'value1');
    $this->settings_service->update_setting('key2', 'value2');

    $all_settings = $this->settings_service->get_all_settings();
    $this->assertEquals('value1', $all_settings['key1']);
    $this->assertEquals('value2', $all_settings['key2']);
  }
}
