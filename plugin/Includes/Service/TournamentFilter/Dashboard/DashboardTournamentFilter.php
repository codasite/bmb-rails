<?php

namespace WStrategies\BMB\Includes\Service\TournamentFilter\Dashboard;

use WStrategies\BMB\Includes\Service\TournamentFilter\TournamentFilterInterface;

class DashboardTournamentFilter implements TournamentFilterInterface {
  private DashboardTournamentsQuery $tournament_query;
  private string $role;
  private string $paged_status;
  private int $per_page;
  private bool $active;

  public function __construct($args = []) {
    $this->role = $args['role'] ?? '';
    $this->paged_status = $args['paged_status'] ?? '';
    $this->per_page = $args['per_page'] ?? 0;
    $this->tournament_query =
      $args['tournament_query'] ?? new DashboardTournamentsQuery();
    $this->active = $args['active'] ?? false;
  }

  public function get_tournaments(int $page): array {
    return $this->tournament_query->get_tournaments(
      $page,
      $this->per_page,
      $this->paged_status,
      $this->role
    );
  }

  public function has_tournaments(): bool {
    return $this->tournament_query->has_tournaments(
      $this->paged_status,
      $this->role
    );
  }

  public function get_count(): int {
    return $this->tournament_query->get_tournaments_count(
      $this->paged_status,
      $this->role
    );
  }

  public function get_max_num_pages(): int {
    return $this->tournament_query->get_max_num_pages(
      $this->per_page,
      $this->paged_status,
      $this->role
    );
  }

  public function is_active(): bool {
    return $this->active;
  }

  public function set_active(bool $active): void {
    $this->active = true;
  }

  public function get_paged_status(): string {
    return $this->paged_status;
  }
}
