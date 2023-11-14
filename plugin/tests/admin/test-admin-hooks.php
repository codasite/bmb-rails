<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';

class AdminHooksTest extends WPBB_UnitTestCase {
  public function test_add_upcoming_tag_should_change_status_to_upcoming() {
    $factory = self::factory()->bracket;
    $bracket = $factory->create_and_get([
      'status' => 'publish',
    ]);
    wp_add_post_tags($bracket->id, 'bmb_upcoming');
    $updated_bracket = $factory->get_object_by_id($bracket->id);
    $this->assertEquals('upcoming', $updated_bracket->status);
  }
  public function test_status_remove_upcoming_tag_should_change_status_to_publish() {
    $factory = self::factory()->bracket;
    $bracket = $factory->create_and_get([
      'status' => 'upcoming',
    ]);
    // remove the upcoming tag
    wp_set_post_terms($bracket->id, '', 'post_tag');
    $updated_bracket = $factory->get_object_by_id($bracket->id);
    $this->assertEquals('publish', $updated_bracket->status);
  }
}
