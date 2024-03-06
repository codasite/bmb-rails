<?php
namespace WStrategies\BMB\Includes;

use WStrategies\BMB\Includes\Repository\BracketMatchRepo;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\BracketResultsRepo;
use WStrategies\BMB\Includes\Repository\NotificationRepo;
use WStrategies\BMB\Includes\Repository\PickRepo;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Repository\TeamRepo;

/**
 * Fired during plugin activation
 *
 * @link       https://https://github.com/barrymolina
 * @since      1.0.0
 *
 * @package    Wp_Bracket_Builder
 * @subpackage Wp_Bracket_Builder/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wp_Bracket_Builder
 * @subpackage Wp_Bracket_Builder/includes
 * @author     Barry Molina <barry@wstrategies.co>
 */
class Activator {
  /**
   * Short Description. (use period)
   *
   * Long Description.
   *
   * @since    1.0.0
   */
  public static function activate(): void {
    BracketRepo::create_table();
    PlayRepo::create_table();
    TeamRepo::create_table();
    BracketMatchRepo::create_table();
    PickRepo::create_table();
    BracketResultsRepo::create_table();
    NotificationRepo::create_table();
  }

  // WARNING: This function will delete all bracket data
  // DO NOT USE IN PRODUCTION
  // @phpstan-ignore-next-line
  private static function delete_tables(): void {
    BracketResultsRepo::drop_table();
    PickRepo::drop_table();
    BracketMatchRepo::drop_table();
    TeamRepo::drop_table();
    NotificationRepo::drop_table();
    PlayRepo::drop_table();
    BracketRepo::drop_table();
  }
}
