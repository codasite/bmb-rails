<?php
namespace WStrategies\BMB\tests\integration\Includes\service\BracketProduct;

use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductUtils;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class BracketProductUtilsTest extends WPBB_UnitTestCase {
  public function test_get_bracket_fee() {
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    update_post_meta($bracket->id, 'bracket_fee', 5.0);
    $utils = new BracketProductUtils();
    $fee = $utils->get_bracket_fee($bracket->id);
    $this->assertEquals(5.0, $fee);
  }

  public function test_get_bracket_fee_none() {
    $bracket = $this->create_bracket([
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
    $bracket = $this->create_bracket([
      'num_teams' => 4,
    ]);
    update_post_meta($bracket->id, 'bracket_fee', 5);
    $utils = new BracketProductUtils();
    $fee = $utils->get_bracket_fee($bracket);
    $this->assertEquals(5.0, $fee);
  }

  public function test_get_bracket_fee_name() {
    $bracket = $this->create_bracket([
      'title' => 'My Bracket',
      'num_teams' => 4,
    ]);
    $utils = new BracketProductUtils();
    $name = $utils->get_bracket_fee_name($bracket->id);
    $this->assertEquals('Tournament fee: My Bracket', $name);
  }
}
