<?php

use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductUtils;

class BracketProductUtilsTest extends WPBB_UnitTestCase {
  public function test_get_bracket_fee() {
    $bracket = self::factory()->bracket->create_and_get([
      'num_teams' => 4,
    ]);
    update_post_meta($bracket->id, 'bracket_fee', 5.0);
    $utils = new BracketProductUtils();
    $fee = $utils->get_bracket_fee($bracket->id);
    $this->assertEquals(5.0, $fee);
  }

  public function test_get_bracket_fee_none() {
    $bracket = self::factory()->bracket->create_and_get([
      'num_teams' => 4,
    ]);
    $utils = new BracketProductUtils();
    $fee = $utils->get_bracket_fee($bracket->id);
    $this->assertEquals(0, $fee);
  }

  public function test_get_bracket_fee_invalid() {
    $utils = new BracketProductUtils();
    update_post_meta(0, 'bracket_fee', 'five');
    $fee = $utils->get_bracket_fee(0);
    $this->assertEquals(0, $fee);
  }

  public function test_get_bracket_fee_int() {
    $bracket = self::factory()->bracket->create_and_get([
      'num_teams' => 4,
    ]);
    update_post_meta($bracket->id, 'bracket_fee', 5);
    $utils = new BracketProductUtils();
    $fee = $utils->get_bracket_fee($bracket);
    $this->assertEquals(5.0, $fee);
  }

  public function test_get_bracket_fee_name() {
    $bracket = self::factory()->bracket->create_and_get([
      'title' => 'My Bracket',
      'num_teams' => 4,
    ]);
    $utils = new BracketProductUtils();
    $name = $utils->get_bracket_fee_name($bracket->id);
    $this->assertEquals('Tournament fee: My Bracket', $name);
  }
}
