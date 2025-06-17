<?php

namespace WStrategies\BMB\Includes\Service;

/**
 * Service class for managing plugin settings
 *
 * @package    Wp_Bracket_Builder
 * @subpackage Wp_Bracket_Builder/includes/service
 */
class SettingsService {
  private string $option_name = 'wpbb_settings';

  /**
   * Get a specific setting value
   *
   * @param string $key The setting key
   * @param mixed $default Default value if setting doesn't exist
   * @return mixed The setting value
   */
  public function get_setting(string $key, $default = null) {
    $options = get_option($this->option_name, []);
    return isset($options[$key]) ? $options[$key] : $default;
  }

  /**
   * Get all settings
   *
   * @return array All settings
   */
  public function get_all_settings(): array {
    return get_option($this->option_name, []);
  }

  /**
   * Update a specific setting
   *
   * @param string $key The setting key
   * @param mixed $value The setting value
   * @return bool Whether the option was updated successfully
   */
  public function update_setting(string $key, $value): bool {
    $options = get_option($this->option_name, []);
    $options[$key] = $value;
    return update_option($this->option_name, $options);
  }

  /**
   * Get the featured brackets count setting
   *
   * @return int Number of featured brackets to display
   */
  public function get_featured_brackets_count(): int {
    return (int) $this->get_setting('featured_brackets_count', 15);
  }

  /**
   * Update the featured brackets count setting
   *
   * @param int $count Number of featured brackets to display
   * @return bool Whether the option was updated successfully
   */
  public function update_featured_brackets_count(int $count): bool {
    return $this->update_setting(
      'featured_brackets_count',
      max(1, min(50, $count))
    );
  }
}
