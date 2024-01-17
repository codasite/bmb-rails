<?php

namespace WStrategies\BMB\Public\Partials\dashboard;

use WStrategies\BMB\Includes\Service\Dashboard\DashboardService;
use WStrategies\BMB\Public\Partials\shared\BracketsCommon;
use WStrategies\BMB\Public\Partials\shared\PaginationWidget;

class TournamentsPage {
  private DashboardService $dashboard_service;

  public function __construct($args = []) {
    $this->dashboard_service =
      $args['dashboard_service'] ?? new DashboardService();
  }

  public function render() {
    $paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;
    $paged_status = get_query_var('status');

    if (empty($paged_status)) {
      $paged_status = 'all';
    }

    $brackets = [];

    ob_start();
    ?>
    <div id="wpbb-tournaments-modals"></div>
      <div class="tw-flex tw-flex-col">
        <h1 class="tw-text-24 sm:tw-text-48 lg:tw-text-64 tw-font-700 tw-leading-none">Tournaments</h1>
        <p class="tw-text-24 tw-my-0 tw-font-500 tw-opacity-50">All tournaments you are participating in</p>
        <div class="tw-flex tw-gap-10 tw-py-24">
          <?php echo BracketsCommon::sort_button(
            'All',
            get_permalink() . 'brackets/?status=all',
            $paged_status === 'all'
          ); ?>
          <?php echo BracketsCommon::sort_button(
            'Active',
            get_permalink() . 'brackets/?status=active',
            $paged_status === 'active'
          ); ?>
          <?php echo BracketsCommon::sort_button(
            'Scored',
            get_permalink() . 'brackets/?status=scored',
            $paged_status === 'scored'
          ); ?>
        </div>
        <div class="tw-flex tw-flex-col tw-gap-15">
          <?php foreach ($brackets as $bracket) {
            echo ManageBracketsPageCommon::bracket_list_item($bracket);
          } ?>
          <?php PaginationWidget::pagination($paged, $num_pages); ?>
        </div>
      </div>
    <?php return ob_get_clean();
  }
}
