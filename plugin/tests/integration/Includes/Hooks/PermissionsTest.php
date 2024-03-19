<?php
namespace WStrategies\BMB\tests\integration\Includes\Hooks;

use WStrategies\BMB\tests\integration\Traits\SetupAdminUser;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class PermissionsTest extends WPBB_UnitTestCase {
  use SetupAdminUser;
  public function test_admin_can_view_bracket_chat() {
    $admin = self::factory()->user->create_and_get(['role' => 'administrator']);
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    wp_set_current_user($admin->ID);
    $this->assertTrue(current_user_can('wpbb_view_bracket_chat', $bracket->id));
  }

  public function test_user_with_printed_play_can_view_chat() {
    $user = self::factory()->user->create_and_get(['role' => 'subscriber']);
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'user_id' => $user->ID,
      'is_printed' => true,
    ]);
    $this->assertTrue(current_user_can('wpbb_view_bracket_chat', $bracket->id));
  }

  public function test_user_with_unprinted_play_cannot_view_chat() {
    $user = self::factory()->user->create_and_get(['role' => 'subscriber']);
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    $play = $this->create_play([
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
    $bracket = $this->create_bracket([
      'num_teams' => 4,
      'author' => $user->ID,
    ]);
    wp_set_current_user($user->ID);
    $this->assertTrue(current_user_can('wpbb_view_bracket_chat', $bracket->id));
  }

  public function test_author_can_view_printed_play() {
    $user = self::factory()->user->create_and_get(['role' => 'subscriber']);
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'author' => $user->ID,
      'is_printed' => true,
    ]);
    wp_set_current_user($user->ID);
    $this->assertTrue(current_user_can('wpbb_view_play', $play->id));
  }

  public function test_author_can_view_unprinted_play() {
    $user = self::factory()->user->create_and_get(['role' => 'subscriber']);
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'author' => $user->ID,
    ]);
    wp_set_current_user($user->ID);
    $this->assertTrue(current_user_can('wpbb_view_play', $play->id));
  }

  public function test_non_author_can_view_printed_play() {
    $user = self::factory()->user->create_and_get(['role' => 'subscriber']);
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_printed' => true,
    ]);
    wp_set_current_user($user->ID);
    $this->assertTrue(current_user_can('wpbb_view_play', $play->id));
  }

  public function test_non_author_cannot_view_unprinted_play() {
    $user = self::factory()->user->create_and_get(['role' => 'subscriber']);
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
    ]);
    wp_set_current_user($user->ID);
    $this->assertFalse(current_user_can('wpbb_view_play', $play->id));
  }

  public function test_author_can_print_printed_play() {
    $user = self::factory()->user->create_and_get(['role' => 'subscriber']);
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'author' => $user->ID,
      'is_printed' => true,
    ]);
    wp_set_current_user($user->ID);
    $this->assertTrue(current_user_can('wpbb_print_play', $play->id));
  }

  public function test_author_can_print_unprinted_play() {
    $user = self::factory()->user->create_and_get(['role' => 'subscriber']);
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'author' => $user->ID,
    ]);
    wp_set_current_user($user->ID);
    $this->assertTrue(current_user_can('wpbb_print_play', $play->id));
  }

  public function test_non_author_can_print_printed_play() {
    $user = self::factory()->user->create_and_get(['role' => 'subscriber']);
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_printed' => true,
    ]);
    wp_set_current_user($user->ID);
    $this->assertTrue(current_user_can('wpbb_print_play', $play->id));
  }

  public function test_non_author_cannot_print_unprinted_play() {
    $user = self::factory()->user->create_and_get(['role' => 'subscriber']);
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
    ]);
    wp_set_current_user($user->ID);
    $this->assertFalse(current_user_can('wpbb_print_play', $play->id));
  }

  public function test_bmb_vip_can_read() {
    $user = self::factory()->user->create_and_get(['role' => 'bmb_vip']);
    wp_set_current_user($user->ID);
    $this->assertTrue(current_user_can('read'));
  }

  public function test_bmb_plus_can_read() {
    $user = self::factory()->user->create_and_get(['role' => 'bmb_plus']);
    wp_set_current_user($user->ID);
    $this->assertTrue(current_user_can('read'));
  }

  public function test_customer_can_view_tournament_entry() {
    $user = self::factory()->user->create_and_get(['role' => 'customer']);
    $bracket = $this->create_bracket();
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
      'is_tournament_entry' => true,
    ]);
    wp_set_current_user($user->ID);
    $this->assertTrue(current_user_can('wpbb_view_play', $play->id));
  }
  public function test_customer_cannot_view_non_tournament_entry() {
    $user = self::factory()->user->create_and_get(['role' => 'customer']);
    $bracket = $this->create_bracket();
    $play = $this->create_play([
      'bracket_id' => $bracket->id,
    ]);
    wp_set_current_user($user->ID);
    $this->assertFalse(current_user_can('wpbb_view_play', $play->id));
  }

  public function test_should_allow_anyone_to_play_unpaid_bracket_for_free() {
    $user = $this->create_user();
    $bracket = $this->create_bracket([
      'fee' => 0,
    ]);
    wp_set_current_user($user->ID);
    $this->assertTrue(
      current_user_can('wpbb_play_bracket_for_free', $bracket->id)
    );
  }

  public function test_should_allow_players_with_paid_play_to_play_paid_bracket_for_free() {
    $user = $this->create_user();
    $bracket = $this->create_bracket([
      'fee' => 10,
    ]);
    $paid_play = $this->create_play([
      'author' => $user->ID,
      'bracket_id' => $bracket->id,
      'is_paid' => true,
    ]);
    wp_set_current_user($user->ID);
    $this->assertTrue(
      current_user_can('wpbb_play_bracket_for_free', $bracket->id)
    );
  }

  public function test_should_not_allow_players_with_unpaid_play_to_play_paid_bracket_for_free() {
    $user = $this->create_user();
    $bracket = $this->create_bracket([
      'fee' => 10,
    ]);
    $unpaid_play = $this->create_play([
      'author' => $user->ID,
      'bracket_id' => $bracket->id,
      'is_paid' => false,
    ]);
    wp_set_current_user($user->ID);
    $this->assertFalse(
      current_user_can('wpbb_play_bracket_for_free', $bracket->id)
    );
  }

  public function test_should_not_allow_players_with_no_plays_to_play_paid_bracket_for_free() {
    $user = $this->create_user();
    $bracket = $this->create_bracket([
      'fee' => 10,
    ]);
    wp_set_current_user($user->ID);
    $this->assertFalse(
      current_user_can('wpbb_play_bracket_for_free', $bracket->id)
    );
  }

  public function test_should_not_allow_players_with_paid_play_for_other_bracket_to_play_paid_bracket_for_free() {
    $user = $this->create_user();
    $bracket = $this->create_bracket([
      'fee' => 10,
    ]);
    $other_bracket = $this->create_bracket();
    $paid_play = $this->create_play([
      'author' => $user->ID,
      'bracket_id' => $other_bracket->id,
      'is_paid' => true,
    ]);
    wp_set_current_user($user->ID);
    $this->assertFalse(
      current_user_can('wpbb_play_bracket_for_free', $bracket->id)
    );
  }

  public function test_should_allow_admin_to_play_paid_bracket_for_free() {
    $admin = $this->create_user(['role' => 'administrator']);
    $bracket = $this->create_bracket([
      'fee' => 10,
    ]);
    wp_set_current_user($admin->ID);
    $this->assertTrue(
      current_user_can('wpbb_play_bracket_for_free', $bracket->id)
    );
  }
}
