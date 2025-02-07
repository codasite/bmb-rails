<?php
namespace WStrategies\BMB\tests\unit\Features\VotingBracket\Notifications;

use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Features\VotingBracket\Notifications\RoundCompleteMessageFormatter;
use WStrategies\BMB\Includes\Domain\Bracket;

class RoundCompleteMessageFormatterTest extends TestCase {
  public function test_get_heading_should_return_complete_text_when_bracket_is_complete() {
    $bracket = new Bracket(['status' => 'complete', 'title' => 'Test Bracket']);
    $heading = RoundCompleteMessageFormatter::get_title($bracket);
    $this->assertEquals('TEST BRACKET Voting Complete!', $heading);
  }

  public function test_get_heading_should_return_round_complete_text_when_bracket_is_not_complete() {
    $bracket = new Bracket(['status' => 'live', 'title' => 'Test Bracket']);
    $heading = RoundCompleteMessageFormatter::get_title($bracket);
    $this->assertEquals('TEST BRACKET Voting Round Complete!', $heading);
  }

  public function test_get_message_should_return_complete_text_when_bracket_is_complete() {
    $bracket = new Bracket(['status' => 'complete', 'title' => 'Test Bracket']);
    $message = RoundCompleteMessageFormatter::get_message($bracket);
    $this->assertEquals('The voting for TEST BRACKET is complete!', $message);
  }

  public function test_get_message_should_return_next_round_text_when_bracket_is_not_complete() {
    $bracket = new Bracket(['status' => 'live', 'live_round_index' => 1]);
    $message = RoundCompleteMessageFormatter::get_message($bracket);
    $this->assertEquals('Vote now in round 2', $message);
  }

  public function test_get_button_text_should_return_view_results_when_bracket_is_complete() {
    $bracket = new Bracket(['status' => 'complete']);
    $text = RoundCompleteMessageFormatter::get_button_text($bracket);
    $this->assertEquals('View Results', $text);
  }

  public function test_get_button_text_should_return_vote_now_when_bracket_is_not_complete() {
    $bracket = new Bracket(['status' => 'live']);
    $text = RoundCompleteMessageFormatter::get_button_text($bracket);
    $this->assertEquals('Vote now', $text);
  }

  public function test_get_link_should_return_results_url_when_bracket_is_complete() {
    $bracket = new Bracket([
      'status' => 'complete',
      'url' => 'https://example.com/bracket/',
    ]);
    $link = RoundCompleteMessageFormatter::get_link($bracket);
    $this->assertEquals('https://example.com/bracket/results', $link);
  }

  public function test_get_link_should_return_play_url_when_bracket_is_not_complete() {
    $bracket = new Bracket([
      'status' => 'live',
      'url' => 'https://example.com/bracket/',
    ]);
    $link = RoundCompleteMessageFormatter::get_link($bracket);
    $this->assertEquals('https://example.com/bracket/play', $link);
  }
}
