<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR . 'public/hooks/class-wpbb-permissions.php';

class PermissionsTest extends WPBB_UnitTestCase {
  public function test_admin_can_view_bracket_chat() {
    $admin = self::factory()->user->create_and_get(['role' => 'administrator']);
    $bracket = self::factory()->bracket->create_object([
      'num_teams' => 4,
    ]);
    wp_set_current_user($admin->ID);
    $this->assertTrue(current_user_can('wpbb_view_bracket_chat', $bracket->id));
  }

  public function test_user_with_printed_play_can_view_chat() {
    $user = self::factory()->user->create_and_get(['role' => 'subscriber']);
    $bracket = self::factory()->bracket->create_object([
      'num_teams' => 4,
    ]);
    $play = self::factory()->play->create_object([
      'bracket_id' => $bracket->id,
      'user_id' => $user->ID,
      'is_printed' => true,
    ]);
    $this->assertTrue(current_user_can('wpbb_view_bracket_chat', $bracket->id));
  }

  public function test_user_with_unprinted_play_cannot_view_chat() {
    $user = self::factory()->user->create_and_get(['role' => 'subscriber']);
    $bracket = self::factory()->bracket->create_object([
      'num_teams' => 4,
    ]);
    $play = self::factory()->play->create_object([
      'bracket_id' => $bracket->id,
      'user_id' => $user->ID,
    ]);
    wp_set_current_user($user->ID);
    $this->assertFalse(
      current_user_can('wpbb_view_bracket_chat', $bracket->id)
    );
  }

  public function test_bracket_author_can_view_chat() {
    $user = self::factory()->user->create_and_get(['role' => 'subscriber']);
    $bracket = self::factory()->bracket->create_object([
      'num_teams' => 4,
      'author' => $user->ID,
    ]);
    wp_set_current_user($user->ID);
    $this->assertTrue(current_user_can('wpbb_view_bracket_chat', $bracket->id));
  }
}
