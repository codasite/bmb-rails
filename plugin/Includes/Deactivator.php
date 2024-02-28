<?php
namespace WStrategies\BMB\Includes;

/**
 * Fired during plugin deactivation
 *
 * @link       https://https://github.com/barrymolina
 * @since      1.0.0
 *
 * @package    Wp_Bracket_Builder
 * @subpackage Wp_Bracket_Builder/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Wp_Bracket_Builder
 * @subpackage Wp_Bracket_Builder/includes
 * @author     Barry Molina <barry@wstrategies.co>
 */
class Deactivator {
  /**
   * Short Description. (use period)
   *
   * Long Description.
   *
   * @since    1.0.0
   */
  public static function deactivate(): void {
    remove_role('bmb_plus');
    remove_role('private_reader');
    $timestamp = wp_next_scheduled('wpbb_notification_cron_hook');
    wp_unschedule_event($timestamp, 'wpbb_notification_cron_hook');
  }
}
