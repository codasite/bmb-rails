<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR .
  'includes/service/bracket-product/class-wpbb-bracket-product-utils.php';

class BracketProductUtilsTest extends WPBB_UnitTestCase {
  public function test_get_bracket_fee() {
    $bracket = self::factory()->bracket->create_and_get([
      'num_teams' => 4,
    ]);
    update_post_meta($bracket->id, 'bmb_fee', 5.0);
    $utils = new Wpbb_BracketProductUtils();
    $fee = $utils->get_bracket_fee($bracket->id);
    $this->assertEquals(5.0, $fee);
  }

  public function test_get_bracket_fee_none() {
    $bracket = self::factory()->bracket->create_and_get([
      'num_teams' => 4,
    ]);
    $utils = new Wpbb_BracketProductUtils();
    $fee = $utils->get_bracket_fee($bracket->id);
    $this->assertEquals(0, $fee);
  }

  public function test_get_bracket_fee_invalid() {
    $utils = new Wpbb_BracketProductUtils();
    update_post_meta(0, 'bmb_fee', 'five');
    $fee = $utils->get_bracket_fee(0);
    $this->assertEquals(0, $fee);
  }

  public function test_get_bracket_fee_int() {
    $bracket = self::factory()->bracket->create_and_get([
      'num_teams' => 4,
    ]);
    update_post_meta($bracket->id, 'bmb_fee', 5);
    $utils = new Wpbb_BracketProductUtils();
    $fee = $utils->get_bracket_fee($bracket);
    $this->assertEquals(5.0, $fee);
  }
}
