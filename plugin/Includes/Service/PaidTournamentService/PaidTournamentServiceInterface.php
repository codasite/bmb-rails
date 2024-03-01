<?php
namespace WStrategies\BMB\Includes\Service\PaidTournamentService;

use WStrategies\BMB\Includes\Domain\Play;

interface PaidTournamentServiceInterface {
  public function on_play_created(Play $play): void;
  /**
   * @param array<mixed> $data
   * @return array<mixed>
   */
  public function filter_play_created_response_data(array $data): array;
}
