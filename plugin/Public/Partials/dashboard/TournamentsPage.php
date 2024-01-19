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

  public function get_role_link(string $label, bool $active, string $url) {
    ob_start(); ?>
    <a class="tw-text-white tw-text-24 tw-font-500<?php echo $active
      ? ''
      : ' tw-opacity-50'; ?> hover:tw-cursor-pointer"
       href="<?php echo $url; ?>"><?php echo $label; ?></a>
    <?php return ob_get_clean();
  }

  public function render() {
    $paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;
    $paged_status = get_query_var('status', 'live');
    $role = get_query_var('role', 'hosting');

    $result = $this->dashboard_service->get_tournaments(
      $paged,
      5,
      $paged_status,
      $role === 'hosting'
    );
    $brackets = $result['brackets'];
    $num_pages = $result['max_num_pages'];

    ob_start();
    ?>
    <div id="wpbb-tournaments-modals"></div>
      <div class="tw-flex tw-flex-col tw-gap-40">
        <div class="tw-flex tw-flex-col tw-gap-16">
          <h1 class="tw-text-24 sm:tw-text-48 lg:tw-text-64 tw-font-700 tw-leading-none">Tournaments</h1>
          <a href="<?php echo get_permalink(
            get_page_by_path('bracket-builder')
          ); ?>" class="tw-flex tw-gap-16 tw-items-center tw-justify-center tw-border-solid tw-border tw-border-white tw-rounded-8 tw-p-16 tw-bg-white/15 tw-text-white tw-font-sans tw-uppercase tw-cursor-pointer hover:tw-text-black hover:tw-bg-white">
            <?php echo file_get_contents(
              WPBB_PLUGIN_DIR . 'Public/assets/icons/signal.svg'
            ); ?>
            <span class="tw-font-700 tw-text-16 sm:tw-text-24 tw-leading-none">Create Tournament</span>
          </a>
        </div>
        <div class="tw-flex tw-flex-col tw-gap-24">
          <div class="tw-flex tw-justify-start tw-gap-40">
            <?php echo $this->get_role_link(
              'Hosting',
              $role === 'hosting',
              get_permalink() .
                'tournaments/?role=hosting&status=' .
                $paged_status
            ); ?>
            <?php echo $this->get_role_link(
              'Playing',
              $role === 'playing',
              get_permalink() .
                'tournaments/?role=playing&status=' .
                $paged_status
            ); ?>
          </div>
          <div class="tw-flex tw-gap-10 tw-flex-wrap">
            <?php echo BracketsCommon::sort_button(
              'Private',
              get_permalink() . 'tournaments/?status=private&role=' . $role,
              $paged_status === 'private',
              'blue',
              true
            ); ?>
            <?php echo BracketsCommon::sort_button(
              'Upcoming',
              get_permalink() . 'tournaments/?status=upcoming&role=' . $role,
              $paged_status === 'upcoming',
              'yellow',
              true
            ); ?>
            <?php echo BracketsCommon::sort_button(
              'Live',
              get_permalink() . 'tournaments/?status=live&role=' . $role,
              $paged_status === 'live',
              'green',
              true
            ); ?>
            <?php echo BracketsCommon::sort_button(
              'Closed',
              get_permalink() . 'tournaments/?status=closed&role=' . $role,
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
      </div>
    <?php return ob_get_clean();
  }
}
