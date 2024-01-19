<?php

namespace WStrategies\BMB\Public\Partials\dashboard;

use WStrategies\BMB\Includes\Service\Dashboard\DashboardService;
use WStrategies\BMB\Public\Partials\shared\BracketListItem;
use WStrategies\BMB\Public\Partials\shared\PagedStatusFilterButtons;
use WStrategies\BMB\Public\Partials\shared\PaginationWidget;

class TournamentsPage {
  private DashboardService $dashboard_service;
  private static int $per_page = 5;

  public function __construct($args = []) {
    $this->dashboard_service =
      $args['dashboard_service'] ?? new DashboardService();
  }

  public function get_paged_status() {
    return get_query_var('status', 'live');
  }

  public function get_role() {
    return get_query_var('role', 'hosting');
  }

  public function get_paged() {
    return get_query_var('paged') ? absint(get_query_var('paged')) : 1;
  }

  public function get_role_link(string $label, bool $active, string $url) {
    ob_start(); ?>
    <a class="tw-text-white tw-text-24 tw-font-500<?php echo $active
      ? ''
      : ' tw-opacity-50'; ?> hover:tw-cursor-pointer"
       href="<?php echo $url; ?>"><?php echo $label; ?></a>
    <?php return ob_get_clean();
  }

  public function get_tournament_counts(bool $hosting) {
    return [
      'private' => $this->dashboard_service->get_tournaments_count(
        'private',
        $hosting
      ),
      'upcoming' => $this->dashboard_service->get_tournaments_count(
        'upcoming',
        $hosting
      ),
      'live' => $this->dashboard_service->get_tournaments_count(
        'live',
        $hosting
      ),
      'closed' => $this->dashboard_service->get_tournaments_count(
        'closed',
        $hosting
      ),
    ];
  }

  public function render_filter_buttons(
    bool $private = false,
    bool $upcoming = false,
    bool $live = false,
    bool $closed = false
  ) {
    $role = $this->get_role();
    $paged_status = $this->get_paged_status();
    ob_start();
    ?>
    <div class="tw-flex tw-gap-10 tw-flex-wrap">
      <?php if ($private) {
        echo PagedStatusFilterButtons::private_filter_button(
          $this->get_filtered_url($role, 'private'),
          $paged_status === 'private'
        );
      } ?>
      <?php if ($upcoming) {
        echo PagedStatusFilterButtons::upcoming_filter_button(
          $this->get_filtered_url($role, 'upcoming'),
          $paged_status === 'upcoming'
        );
      } ?>
      <?php if ($live) {
        echo PagedStatusFilterButtons::live_filter_button(
          $this->get_filtered_url($role, 'live'),
          $paged_status === 'live'
        );
      } ?>
      <?php if ($closed) {
        echo PagedStatusFilterButtons::closed_filter_button(
          $this->get_filtered_url($role, 'closed'),
          $paged_status === 'closed'
        );
      } ?>
    </div>
    <?php return ob_get_clean();
  }

  public function get_filtered_url(string $role, string $status) {
    return get_permalink() . 'tournaments?role=' . $role . '&status=' . $status;
  }

  public function render() {
    $role = $this->get_role();
    $paged_status = $this->get_paged_status();
    $paged = $this->get_paged();
    $hosting = $role === 'hosting';

    $brackets = $this->dashboard_service->get_tournaments(
      $paged,
      self::$per_page,
      $paged_status,
      $hosting
    );
    $counts = $this->get_tournament_counts($hosting);
    $num_pages = ceil($counts[$paged_status] / self::$per_page);

    $show_private_filter = $counts['private'] > 0;
    $show_upcoming_filter = $counts['upcoming'] > 0;
    $show_live_filter = $counts['live'] > 0;
    $show_closed_filter = $counts['closed'] > 0;

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
              $hosting,
              $this->get_filtered_url('hosting', $paged_status)
            ); ?>
            <?php echo $this->get_role_link(
              'Playing',
              $role === 'playing',
              $this->get_filtered_url('playing', $paged_status)
            ); ?>
          </div>
          <?php echo $this->render_filter_buttons(
            $show_private_filter,
            $show_upcoming_filter,
            $show_live_filter,
            $show_closed_filter
          ); ?>
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
