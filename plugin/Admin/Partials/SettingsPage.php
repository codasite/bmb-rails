<?php

namespace WStrategies\BMB\Admin\Partials;

/**
 * Settings page for the WP Bracket Builder plugin
 *
 * @package    Wp_Bracket_Builder
 * @subpackage Wp_Bracket_Builder/admin/partials
 */
class SettingsPage {
  private string $option_group = 'wpbb_settings';
  private string $option_name = 'wpbb_settings';
  private string $page_slug = 'wpbb-settings';

  public function __construct() {
    add_action('admin_menu', [$this, 'add_settings_page']);
    add_action('admin_init', [$this, 'register_settings']);
  }

  /**
   * Add the settings page to the admin menu
   */
  public function add_settings_page(): void {
    add_options_page(
      'WP Bracket Builder Settings',
      'Bracket Builder',
      'manage_options',
      $this->page_slug,
      [$this, 'render_settings_page']
    );
  }

  /**
   * Register the settings
   */
  public function register_settings(): void {
    register_setting($this->option_group, $this->option_name, [
      $this,
      'sanitize_settings',
    ]);

    add_settings_section(
      'wpbb_general_settings',
      'General Settings',
      [$this, 'render_general_section'],
      $this->page_slug
    );

    add_settings_field(
      'featured_brackets_count',
      'Featured Brackets Count',
      [$this, 'render_featured_brackets_field'],
      $this->page_slug,
      'wpbb_general_settings'
    );
  }

  /**
   * Render the settings page
   */
  public function render_settings_page(): void {
    ?>
    <div class="wrap">
      <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
      <form method="post" action="options.php">
        <?php
        settings_fields($this->option_group);
        do_settings_sections($this->page_slug);
        submit_button();?>
      </form>
    </div>
    <?php
  }

  /**
   * Render the general settings section description
   */
  public function render_general_section(): void {
    echo '<p>Configure general settings for the WP Bracket Builder plugin.</p>';
  }

  /**
   * Render the featured brackets count field
   */
  public function render_featured_brackets_field(): void {
    $options = get_option($this->option_name, []);
    $value = isset($options['featured_brackets_count'])
      ? $options['featured_brackets_count']
      : 15;
    ?>
    <input type="number" 
           id="featured_brackets_count" 
           name="<?php echo esc_attr(
             $this->option_name
           ); ?>[featured_brackets_count]" 
           value="<?php echo esc_attr($value); ?>" 
           min="1" 
           max="50" 
           class="regular-text" />
    <p class="description">
      Number of featured brackets to display on the bracket board page (default: 15, max: 50)
    </p>
    <?php
  }

  /**
   * Sanitize the settings before saving
   */
  public function sanitize_settings($input): array {
    $sanitized = [];

    if (isset($input['featured_brackets_count'])) {
      $count = intval($input['featured_brackets_count']);
      $sanitized['featured_brackets_count'] = max(1, min(50, $count));
    }

    return $sanitized;
  }
}
