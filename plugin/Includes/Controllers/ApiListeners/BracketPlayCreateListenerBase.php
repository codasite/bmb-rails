<?php
namespace WStrategies\BMB\Includes\Controllers\ApiListeners;

use WStrategies\BMB\Includes\Domain\BracketPlay;

abstract class BracketPlayCreateListenerBase implements
  BracketPlayCreateListenerInterface {
  /**
   * @param array<mixed> $data
   * @return array<mixed>
   */
  public function filter_request_params(array $data): array {
    return $data;
  }
  public function filter_before_play_added(BracketPlay $play): BracketPlay {
    return $play;
  }
  public function filter_after_play_added(BracketPlay $play): BracketPlay {
    return $play;
  }
  /**
   * @param array<mixed> $data
   * @return array<mixed>
   */
  public function filter_after_play_serialized(array $data): array {
    return $data;
  }
}
