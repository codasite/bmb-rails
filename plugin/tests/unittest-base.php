<?php

use WStrategies\BMB\Includes\Activator;

include_once 'factory/unittest-factory.php';

/**
 * Class WPBB_UnitTestCase
 *
 *
 * This class is used to set up the plugin's custom tables for unit testing
 */
abstract class WPBB_UnitTestCase extends WP_UnitTestCase {
  protected $plugin_path = '/var/www/html/wp-content/plugins/wp-bracket-builder/';

  protected static function factory() {
    static $factory = null;
    if (!$factory) {
      $factory = new WPBB_UnitTest_Factory();
    }
    return $factory;
  }

  public static function set_up_before_class() {
    parent::set_up_before_class();

    $activator = new Activator();
    $activator->activate();
  }

  public function set_up() {
    parent::set_up();
    $admin_user = self::factory()->user->create([
      'role' => 'administrator',
    ]);
    wp_set_current_user($admin_user);
  }
}
