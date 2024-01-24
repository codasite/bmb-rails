<?php
namespace WStrategies\BMB\Includes\Service\Play;

use WStrategies\BMB\Includes\Controllers\ApiListeners\BracketPlayCreateListenerBase;
use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Utils;

class CurrentPlayService extends BracketPlayCreateListenerBase {
  private Utils $utils;
  private bool $should_set_cookie = true;

  /**
   * @param array<mixed> $args
   */
  public function __construct($args = []) {
    $this->utils = $args['utils'] ?? new Utils();
  }

  public function filter_request_params(array $data): array {
    if (isset($data['set_cookie']) && $data['set_cookie'] === false) {
      $this->should_set_cookie = false;
    }
    return $data;
  }

  public function filter_after_play_added(BracketPlay $play): BracketPlay {
    if ($this->should_set_cookie) {
      $this->utils->set_cookie('play_id', $play->id, ['days' => 30]);
    }
    return $play;
  }
}
