<?php
namespace WStrategies\BMB\tests\unit\Features\Bracket\BracketResults;

use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Features\Bracket\BracketResults\BracketResultsMessageFormatter;
use WStrategies\BMB\Includes\Domain\Fakes\PickResultFakeFactory;

class BracketResultsMessageFormatterTest extends TestCase {
  public function test_get_heading_should_return_won_text_when_pick_is_correct() {
    $pick_result = PickResultFakeFactory::get_correct_pick_result();
    $heading = BracketResultsMessageFormatter::get_message($pick_result);
    $this->assertEquals('You picked TEAM 1... and they won!', $heading);
  }

  public function test_get_heading_should_return_lost_text_when_pick_is_incorrect() {
    $pick_result = PickResultFakeFactory::get_incorrect_pick_result();
    $heading = BracketResultsMessageFormatter::get_message($pick_result);
    $this->assertEquals(
      'You picked TEAM 2... but TEAM 1 won the round!',
      $heading
    );
  }

  public function test_get_title_should_return_correct_text() {
    $title = BracketResultsMessageFormatter::get_title();
    $this->assertEquals('Bracket Results Updated', $title);
  }

  public function test_get_link_should_return_play_url_with_view_suffix() {
    /** @var \WStrategies\BMB\Includes\Domain\Play&\PHPUnit\Framework\MockObject\MockObject $play */
    $play = $this->getMockBuilder(\WStrategies\BMB\Includes\Domain\Play::class)
      ->disableOriginalConstructor()
      ->getMock();

    $play->url = 'https://example.com/bracket/';

    $link = BracketResultsMessageFormatter::get_link($play);
    $this->assertEquals('https://example.com/bracket/view', $link);
  }
}
