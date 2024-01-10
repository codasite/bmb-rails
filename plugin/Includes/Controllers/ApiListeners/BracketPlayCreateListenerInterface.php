<?php
namespace WStrategies\BMB\Includes\Controllers\ApiListeners;

use WStrategies\BMB\Includes\Domain\BracketPlay;

interface BracketPlayCreateListenerInterface {
  /**
   * @param array<mixed> $data
   * @return array<mixed>
   */
  public function filter_request_params(array $data): array;
  public function filter_before_play_added(BracketPlay $play): BracketPlay;
  public function filter_after_play_added(BracketPlay $play): BracketPlay;
  /**
   * @param array<mixed> $data
   * @return array<mixed>
   */
  public function filter_after_play_serialized(array $data): array;
}
