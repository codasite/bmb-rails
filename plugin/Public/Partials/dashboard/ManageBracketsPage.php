<?php

namespace WStrategies\BMB\Public\Partials\dashboard;

use WStrategies\BMB\Includes\Service\Dashboard\DashboardService;
use WStrategies\BMB\Public\Partials\shared\BracketListItem;
use WStrategies\BMB\Public\Partials\shared\BracketsCommon;
use WStrategies\BMB\Public\Partials\shared\PaginationWidget;

class ManageBracketsPage {
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
    $result = $this->dashboard_service->get_hosted_tournaments(
      $paged,
      5,
      $paged_status
    );
    $num_pages = $result['max_num_pages'];
    $brackets = $result['brackets'];
    ob_start();
    ?>
<div id="wpbb-manage-brackets-modals"></div>
  <div class="tw-flex tw-flex-col">
    <h1 class="tw-text-24 sm:tw-text-48 lg:tw-text-64 tw-font-700 tw-leading-none">Manage Brackets</h1>
    <p class="tw-text-24 tw-my-0 tw-font-500 tw-opacity-50">Brackets and tournaments you created</p>
    <a href="<?php echo get_permalink(
      get_page_by_path('bracket-builder')
    ); ?>" class="tw-flex tw-gap-16 tw-items-center tw-justify-center tw-border-solid tw-border tw-border-white tw-rounded-8 tw-p-16 tw-bg-white/15 tw-text-white tw-font-sans tw-uppercase tw-cursor-pointer hover:tw-text-black hover:tw-bg-white tw-mt-24">
      <?php echo file_get_contents(
        WPBB_PLUGIN_DIR . 'Public/assets/icons/signal.svg'
      ); ?>
      <span class="tw-font-700 tw-text-16 sm:tw-text-24 tw-leading-none">Create Bracket</span>
    </a>
    <div class="tw-flex tw-gap-10 tw-py-24 tw-flex-wrap">
      <?php echo BracketsCommon::filter_button(
        'All',
        get_permalink() . 'brackets/?status=all',
        $paged_status === 'all'
      ); ?>
      <?php echo BracketsCommon::filter_button(
        'Active',
        get_permalink() . 'brackets/?status=active',
        $paged_status === 'active'
      ); ?>
      <?php echo BracketsCommon::filter_button(
        'Scored',
        get_permalink() . 'brackets/?status=scored',
        $paged_status === 'scored'
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
