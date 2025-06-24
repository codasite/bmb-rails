<?php

namespace WStrategies\BMB\Includes\Service\TournamentFilter\Public;

use WStrategies\BMB\Includes\Service\TournamentFilter\TournamentFilterInterface;

class PublicBracketFilter implements TournamentFilterInterface {
  private PublicBracketsQuery $brackets_query;
  private string $paged_status;
  private int $per_page;
  private bool $active;
  private array $exclude_tags;

  public function __construct($args = []) {
    $this->paged_status = $args['paged_status'] ?? '';
    $this->per_page = $args['per_page'] ?? 0;
    $this->brackets_query =
      $args['brackets_query'] ?? new PublicBracketsQuery();
    $this->active = $args['active'] ?? false;
    $this->exclude_tags = $args['exclude_tags'] ?? [];
  }

  public function get_tournaments(int $page): array {
    return $this->brackets_query->get_brackets(
      $page,
      $this->per_page,
      $this->paged_status,
      $this->exclude_tags
    );
  }

  public function has_tournaments(): bool {
    return $this->brackets_query->has_brackets(
      $this->paged_status,
      $this->exclude_tags
    );
  }

  public function get_count(): int {
    return $this->brackets_query->get_brackets_count(
      $this->paged_status,
      $this->exclude_tags
    );
  }

  public function get_max_num_pages(): int {
    return $this->brackets_query->get_max_num_pages(
      $this->per_page,
      $this->paged_status,
      $this->exclude_tags
    );
  }

  public function is_active(): bool {
    return $this->active;
  }

  public function set_active(bool $active): void {
    $this->active = $active;
  }

  public function get_paged_status(): string {
    return $this->paged_status;
  }
}
