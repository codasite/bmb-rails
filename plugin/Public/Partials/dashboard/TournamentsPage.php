<?php

namespace WStrategies\BMB\Public\Partials\dashboard;

use WStrategies\BMB\Includes\Service\Dashboard\DashboardService;
use WStrategies\BMB\Public\Partials\shared\BracketListItem;
use WStrategies\BMB\Public\Partials\shared\PagedStatusFilterButtons;
use WStrategies\BMB\Public\Partials\shared\PaginationWidget;

class TournamentsPage {
  private DashboardService $dashboard_service;
  private string $paged;
  private string $role;
  private string $paged_status;
  private static int $PER_PAGE = 5;
  private static string $DEFAULT_ROLE = 'hosting';
  private static string $DEFAULE_HOSTING_STATUS = 'private';
  private static string $DEFAULT_PLAYING_STATUS = 'live';

  public function __construct($args = []) {
    $this->dashboard_service =
      $args['dashboard_service'] ?? new DashboardService();
  }

  public function init() {
    $this->paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;
    $role = get_query_var('role', self::$DEFAULT_ROLE);
    $paged_status = get_query_var('status', $this->get_default_status($role));
    if (!$this->dashboard_service->has_tournaments($paged_status, $role)) {
      $paged_status = $this->get_default_status($role);
    }
    $this->role = $role;
    $this->paged_status = $paged_status;
  }

  private function get_default_status(string $role) {
    return $role === 'hosting'
      ? self::$DEFAULE_HOSTING_STATUS
      : self::$DEFAULT_PLAYING_STATUS;
  }

  public function get_role_link(string $label, bool $active, string $url) {
    ob_start(); ?>
    <a class="tw-text-white tw-text-24 tw-font-500<?php echo $active
      ? ''
      : ' tw-opacity-50'; ?> hover:tw-cursor-pointer"
       href="<?php echo $url; ?>"><?php echo $label; ?></a>
    <?php return ob_get_clean();
  }

  public function get_tournament_counts(string $role) {
    return [
      'private' => $this->dashboard_service->get_tournaments_count(
        'private',
        $role
      ),
      'upcoming' => $this->dashboard_service->get_tournaments_count(
        'upcoming',
        $role
      ),
      'live' => $this->dashboard_service->get_tournaments_count('live', $role),
      'closed' => $this->dashboard_service->get_tournaments_count(
        'closed',
        $role
      ),
    ];
  }

  public function render_filter_buttons() {
    $show_private = $this->dashboard_service->has_tournaments(
      'private',
      $this->role
    );
    $show_upcoming = $this->dashboard_service->has_tournaments(
      'upcoming',
      $this->role
    );
    $show_live = $this->dashboard_service->has_tournaments('live', $this->role);
    $show_closed = $this->dashboard_service->has_tournaments(
      'closed',
      $this->role
    );

    ob_start();
    ?>
    <div class="tw-flex tw-gap-10 tw-flex-wrap">
      <?php if ($show_private) {
        echo PagedStatusFilterButtons::private_filter_button(
          $this->get_filtered_url($this->role, 'private'),
          $this->paged_status === 'private'
        );
      } ?>
      <?php if ($show_upcoming) {
        echo PagedStatusFilterButtons::upcoming_filter_button(
          $this->get_filtered_url($this->role, 'upcoming'),
          $this->paged_status === 'upcoming'
        );
      } ?>
      <?php if ($show_live) {
        echo PagedStatusFilterButtons::live_filter_button(
          $this->get_filtered_url($this->role, 'live'),
          $this->paged_status === 'live'
        );
      } ?>
      <?php if ($show_closed) {
        echo PagedStatusFilterButtons::closed_filter_button(
          $this->get_filtered_url($this->role, 'closed'),
          $this->paged_status === 'closed'
        );
      } ?>
    </div>
    <?php return ob_get_clean();
  }

  public function get_filtered_url(string $role, string $status) {
    return get_permalink() . 'tournaments?role=' . $role . '&status=' . $status;
  }

  public function render() {
    $this->init();

    $brackets = $this->dashboard_service->get_tournaments(
      $this->paged,
      self::$PER_PAGE,
      $this->paged_status,
      $this->role
    );

    $num_pages = $this->dashboard_service->get_max_num_pages(
      self::$PER_PAGE,
      $this->paged_status,
      $this->role
    );

    $hosting = $this->role === 'hosting';

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
              $this->get_filtered_url('hosting', $this->paged_status)
            ); ?>
            <?php echo $this->get_role_link(
              'Playing',
              $this->role === 'playing',
              $this->get_filtered_url('playing', $this->paged_status)
            ); ?>
          </div>
          <?php echo $this->render_filter_buttons(); ?>
          <div class="tw-flex tw-flex-col tw-gap-15">
            <?php foreach ($brackets as $bracket) {
              echo BracketListItem::bracket_list_item($bracket);
            } ?>
            <?php PaginationWidget::pagination($this->paged, $num_pages); ?>
          </div>
        </div>
      </div>
    <?php return ob_get_clean();
  }
}
