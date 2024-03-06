<?php
namespace WStrategies\BMB\tests\unit\Includes\Hooks;


use WP_Mock\Tools\TestCase;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Hooks\BracketChatHooks;
use WStrategies\BMB\Includes\Repository\BracketRepo;

class BracketChatHooksTest extends TestCase {
  public function test_filter_comments_open_true() {
    $bracket_repo_mock = $this->getMockBuilder(BracketRepo::class)
      ->disableOriginalConstructor()
      ->getMock();
    $bracket_mock = $this->getMockBuilder(Bracket::class)
      ->disableOriginalConstructor()
      ->getMock();
    $bracket_mock->method('is_chat_enabled')->willReturn(true);
    $bracket_repo_mock->method('get')->willReturn($bracket_mock);
    $hooks = new BracketChatHooks([
      'bracket_repo' => $bracket_repo_mock,
    ]);
    $this->assertTrue($hooks->filter_comments_open(false, 1));
  }

  public function test_filter_comments_open_false() {
    $bracket_repo_mock = $this->getMockBuilder(BracketRepo::class)
      ->disableOriginalConstructor()
      ->getMock();
    $bracket_mock = $this->getMockBuilder(Bracket::class)
      ->disableOriginalConstructor()
      ->getMock();
    $bracket_mock->method('is_chat_enabled')->willReturn(false);
    $bracket_repo_mock->method('get')->willReturn($bracket_mock);
    $hooks = new BracketChatHooks([
      'bracket_repo' => $bracket_repo_mock,
    ]);
    $this->assertFalse($hooks->filter_comments_open(true, 1));
  }
}
