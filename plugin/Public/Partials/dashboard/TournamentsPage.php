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

    $result = $this->dashboard_service->get_tournaments(
      $paged,
      5,
      $paged_status
    );
    $brackets = $result['brackets'];
    $num_pages = $result['max_num_pages'];

    ob_start();
    ?>
    <div id="wpbb-tournaments-modals"></div>
      <div class="tw-flex tw-flex-col">
        <h1 class="tw-text-24 sm:tw-text-48 lg:tw-text-64 tw-font-700 tw-leading-none">Tournaments</h1>
        <p class="tw-text-24 tw-my-0 tw-font-500 tw-opacity-50">All tournaments you are participating in</p>
        <div class="tw-flex tw-gap-10 tw-py-24 tw-flex-wrap">
          <?php echo BracketsCommon::sort_button(
            'Live',
            get_permalink() . 'tournaments/?status=live',
            $paged_status === 'live',
            'green',
            true
          ); ?>
          <?php echo BracketsCommon::sort_button(
            'Upcoming',
            get_permalink() . 'tournaments/?status=upcoming',
            $paged_status === 'upcoming',
            'yellow',
            true
          ); ?>
          <?php echo BracketsCommon::sort_button(
            'Closed',
            get_permalink() . 'tournaments/?status=closed',
            $paged_status === 'closed',
            'white',
            true
          ); ?>
        </div>
        <div class="tw-flex tw-flex-col tw-gap-15">
          <?php foreach ($brackets as $bracket) {
            echo BracketListItem::bracket_list_item($bracket);
          } ?>
          <?php PaginationWidget::pagination($paged, $num_pages); ?>
        </div>
      </div>
    <?php return ob_get_clean();
  }
}
