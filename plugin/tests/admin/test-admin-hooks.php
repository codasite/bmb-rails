<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';

class AdminHooksTest extends WPBB_UnitTestCase {
  public function test_status_set_to_upcoming() {
    $bracket = self::factory()->bracket->create_and_get([
      'status' => 'publish',
    ]);

    echo 'updating tags';
    wp_add_post_tags($bracket->id, 'bmb_upcoming');
    echo 'updated tags';

    $this->assertEquals('upcoming', $bracket->status);
  }

  public function test_remove_upcoming_status() {
    $bracket = self::factory()->bracket->create_and_get();

    wp_add_post_tags($bracket->id, 'bmb_upcoming');

    wp_remove_object_terms($bracket->id, 'bmb_upcoming', 'post_tag');

    $this->assertEquals('publish', $bracket->status);
  }
}
