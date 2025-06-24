<?php

namespace WStrategies\BMB\Includes\Service;

use WStrategies\BMB\Includes\Service\TournamentFilter\TournamentFilterInterface;
use WStrategies\BMB\Public\Partials\shared\FilterButton;

class FilterPageService {
  private int $paged;
  private string $paged_status;
  private array $filter_buttons = [];
  private array $filters = [];

  public function __construct() {
    $this->paged = (int) get_query_var('paged')
      ? absint(get_query_var('paged'))
      : 1;
    $this->paged_status = get_query_var('status', '');
  }

  /**
   * Initialize filters and filter buttons
   *
   * @param array $filter_data Array of filter configurations
   * @param callable $filter_factory Function to create filter instances
   * @param callable $url_generator Function to generate filtered URLs
   * @return array Array containing 'filters' and 'filter_buttons'
   */
  public function init_filters(
    array $filter_data,
    callable $filter_factory,
    callable $url_generator
  ): array {
    $this->filters = [];
    $this->filter_buttons = [];

    foreach ($filter_data as $data) {
      $filter = $filter_factory($data);
      $this->filters[] = $filter;

      $this->filter_buttons[] = new FilterButton([
        'tournament_filter' => $filter,
        'label' => $data['label'],
        'color' => $data['color'],
        'show_circle' => $data['show_circle'] ?? false,
        'fill_circle' => $data['fill_circle'] ?? false,
        'url' => $url_generator($data['paged_status']),
      ]);
    }

    return [
      'filters' => $this->filters,
      'filter_buttons' => $this->filter_buttons,
    ];
  }

  /**
   * Set the active filter based on queried status or first available filter
   */
  public function set_active_filter(): void {
    // determine which filter to set active
    $queried_filter = $this->get_filter_by_status($this->paged_status);
    if ($queried_filter && $queried_filter->has_tournaments()) {
      $queried_filter->set_active(true);
    } else {
      // activate the first filter that has tournaments
      foreach ($this->filters as $filter) {
        if ($filter->has_tournaments()) {
          $filter->set_active(true);
          $this->paged_status = $filter->get_paged_status();
          $this->paged = 1;
          break;
        }
      }
    }
  }

  /**
   * Get filter by status
   */
  private function get_filter_by_status(
    string $paged_status
  ): ?TournamentFilterInterface {
    foreach ($this->filters as $filter) {
      if ($filter->get_paged_status() === $paged_status) {
        return $filter;
      }
    }
    return null;
  }

  /**
   * Render filter buttons HTML
   */
  public function render_filter_buttons(): string {
    ob_start(); ?>
    <div class="tw-flex tw-gap-10 tw-flex-wrap">
      <?php foreach ($this->filter_buttons as $button) {
        if ($button->get_filter()->has_tournaments()) {
          echo $button->render();
        }
      } ?>
    </div>
    <?php return ob_get_clean();
  }

  /**
   * Get the active filter
   */
  public function get_active_filter(): ?TournamentFilterInterface {
    foreach ($this->filters as $filter) {
      if ($filter->is_active()) {
        return $filter;
      }
    }
    return null;
  }

  /**
   * Get current page number
   */
  public function get_paged(): int {
    return $this->paged;
  }

  /**
   * Get current status
   */
  public function get_paged_status(): string {
    return $this->paged_status;
  }

  /**
   * Get all filters
   */
  public function get_filters(): array {
    return $this->filters;
  }

  /**
   * Get all filter buttons
   */
  public function get_filter_buttons(): array {
    return $this->filter_buttons;
  }
}
