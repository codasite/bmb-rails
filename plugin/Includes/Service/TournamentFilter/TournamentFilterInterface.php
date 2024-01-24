<?php

namespace WStrategies\BMB\Includes\Service\TournamentFilter;

interface TournamentFilterInterface {
  public function get_tournaments(int $page): array;
  public function has_tournaments(): bool;
  public function get_count(): int;
  public function get_max_num_pages(): int;
  public function is_active(): bool;
  public function set_active(bool $active): void;
  public function get_paged_status(): string;
}
