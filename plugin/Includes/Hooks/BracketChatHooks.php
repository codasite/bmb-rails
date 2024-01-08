<?php

namespace WStrategies\BMB\Includes\Hooks;

use WStrategies\BMB\Includes\Loader;
use WStrategies\BMB\Includes\Repository\BracketRepo;

class BracketChatHooks implements HooksInterface {
  public BracketRepo $bracket_repo;

  /**
   * @param array<string, mixed> $opts
   */
  public function __construct(array $opts = []) {
    // ConstructorArgs::load($this, $opts);
    if (
      isset($opts['bracket_repo']) &&
      $opts['bracket_repo'] instanceof BracketRepo
    ) {
      $this->bracket_repo = $opts['bracket_repo'];
    } else {
      $this->bracket_repo = new BracketRepo();
    }
  }
  public function load(Loader $loader): void {
    add_action('comments_open', [$this, 'filter_comments_open'], 10, 3);
  }
  public function filter_comments_open(bool $open, int $post_id): bool {
    $bracket = $this->bracket_repo->get($post_id);
    if ($bracket) {
      return $bracket->is_chat_enabled();
    }
    return $open;
  }
}
