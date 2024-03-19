<?php
namespace WStrategies\BMB\tests\integration\Includes\service\Play;

use WStrategies\BMB\Includes\Service\Play\FreePaidPlayService;
use WStrategies\BMB\tests\integration\WPBB_UnitTestCase;

class FreePaidPlayServiceTest extends WPBB_UnitTestCase {
  public function test_should_mark_play_as_paid_if_user_has_paid_play() {
    $user = $this->create_user();
    $bracket = $this->create_bracket([
      'fee' => 10,
    ]);
    $paid_play = $this->create_play([
      'author' => $user->ID,
      'bracket_id' => $bracket->id,
      'is_paid' => true,
    ]);
    $new_play = $this->create_play([
      'author' => $user->ID,
      'bracket_id' => $bracket->id,
      'is_paid' => false,
    ]);
    $sot = new FreePaidPlayService();
    wp_set_current_user($user->ID);
    $new_play = $sot->filter_before_play_added($new_play);
    $this->assertTrue($new_play->is_paid);
  }

  public function test_should_not_mark_as_paid_if_user_has_no_paid_play() {
    $user = $this->create_user();
    $bracket = $this->create_bracket([
      'fee' => 10,
    ]);
    $new_play = $this->create_play([
      'author' => $user->ID,
      'bracket_id' => $bracket->id,
      'is_paid' => false,
    ]);
    $sot = new FreePaidPlayService();
    wp_set_current_user($user->ID);
    $new_play = $sot->filter_before_play_added($new_play);
    $this->assertFalse($new_play->is_paid);
  }
}
