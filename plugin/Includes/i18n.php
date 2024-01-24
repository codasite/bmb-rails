<?php
namespace WStrategies\BMB\Includes;

use WStrategies\BMB\Includes\Hooks\HooksInterface;
use WStrategies\BMB\Includes\Hooks\Loader;

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://https://github.com/barrymolina
 * @since      1.0.0
 *
 * @package    Wp_Bracket_Builder
 * @subpackage Wp_Bracket_Builder/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Wp_Bracket_Builder
 * @subpackage Wp_Bracket_Builder/includes
 * @author     Barry Molina <barry@wstrategies.co>
 */
class i18n implements HooksInterface {
  public function load(Loader $loader): void {
    $loader->add_action('plugins_loaded', [$this, 'load_plugin_textdomain']);
  }
  /**
   * Load the plugin text domain for translation.
   *
   * @since    1.0.0
   */
  public function load_plugin_textdomain(): void {
    load_plugin_textdomain(
      'wp-bracket-builder',
      false,
      dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
    );
  }
}
