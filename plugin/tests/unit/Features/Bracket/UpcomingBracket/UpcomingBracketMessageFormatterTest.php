<?php
namespace WStrategies\BMB\tests\unit\Features\Bracket\UpcomingBracket;

use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Features\Bracket\UpcomingBracket\UpcomingBracketMessageFormatter;
use WStrategies\BMB\Includes\Domain\Bracket;

class UpcomingBracketMessageFormatterTest extends TestCase {
  public function test_get_heading_should_return_correct_text() {
    $bracket = new Bracket(['title' => 'Test Bracket']);
    $heading = UpcomingBracketMessageFormatter::get_message($bracket);
    $this->assertEquals('TEST BRACKET is now live. Make your picks!', $heading);
  }
}
