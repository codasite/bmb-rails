<?php

use WStrategies\BMB\Includes\Activator;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\tests\integration\factory\TestFactory;

include_once 'Traits/SetupAdminUser.php';

/**
 * Class WPBB_UnitTestCase
 *
 *
 * This class is used to set up the plugin's custom tables for unit testing
 */
abstract class WPBB_UnitTestCase extends WP_UnitTestCase {
  protected $plugin_path = '/var/www/html/wp-content/plugins/wp-bracket-builder/';

  protected static function factory(): ?TestFactory {
    static $factory = null;
    if (!$factory) {
      $factory = new TestFactory();
    }
    return $factory;
  }

  /**
   * @beforeClass
   */
  public static function set_up_before_class(): void {
    parent::set_up_before_class();

    $activator = new Activator();
    $activator->activate();
  }

  public function create_bracket($args = []): Bracket {
    return self::factory()->bracket->create_and_get($args);
  }

  public function create_play($args = []) {
    return self::factory()->play->create_and_get($args);
  }

  public function update_bracket(
    $bracket,
    $args = []
  ): WP_Error|Bracket|int|null {
    return self::factory()->bracket->update_object($bracket, $args);
  }

  public function update_play(
    $play,
    $args = []
  ): \WStrategies\BMB\Includes\Domain\Play|WP_Error|int|null {
    return self::factory()->play->update_object($play, $args);
  }

  public function get_play($play_id) {
    return self::factory()->play->get_object_by_id($play_id);
  }

  public function get_bracket(int $bracket_id): Bracket {
    return self::factory()->bracket->get_object_by_id($bracket_id);
  }

  public function create_post($args = []): WP_Error|WP_Post {
    return self::factory()->post->create_and_get($args);
  }

  public function create_user($args = []): WP_Error|WP_User {
    return self::factory()->user->create_and_get($args);
  }
}
