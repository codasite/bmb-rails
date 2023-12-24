<?php

use WStrategies\BMB\Includes\Activator;
use WStrategies\BMB\Includes\Domain\Bracket;

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

  public function create_bracket($args = []): Bracket {
    return self::factory()->bracket->create_and_get($args);
  }

  public function create_play($args = []) {
    return self::factory()->play->create_and_get($args);
  }

  public function update_bracket($bracket, $args = []) {
    return self::factory()->bracket->update_object($bracket, $args);
  }

  public function update_play($play, $args = []) {
    return self::factory()->play->update_object($play, $args);
  }

  public function get_play($play_id) {
    return self::factory()->play->get_object_by_id($play_id);
  }

  public function create_post($args = []) {
    return self::factory()->post->create_and_get($args);
  }

  public function create_user($args = []) {
    return self::factory()->user->create_and_get($args);
  }
}
