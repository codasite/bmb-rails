<?php
namespace WStrategies\BMB\tests\unit\Includes\Domain;

use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Includes\Domain\Bracket;

class BracketTest extends TestCase {
  public function test_chat_is_enabled_for_published_bracket() {
    $bracket = new Bracket([
      'status' => 'publish',
    ]);
    $this->assertTrue($bracket->is_chat_enabled());
  }

  public function test_chat_is_disabled_for_private_bracket() {
    $bracket = new Bracket([
      'status' => 'draft',
    ]);
    $this->assertFalse($bracket->is_chat_enabled());
  }

  public function test_chate_is_disabled_for_upcoming_bracket() {
    $bracket = new Bracket([
      'status' => 'future',
    ]);
    $this->assertFalse($bracket->is_chat_enabled());
  }
}
