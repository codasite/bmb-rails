<?php

namespace WStrategies\BMB\Public\Partials\dashboard;

use WStrategies\BMB\Includes\Service\TournamentFilter\Dashboard\DashboardTournamentFilter;
use WStrategies\BMB\Includes\Service\TournamentFilter\Dashboard\DashboardTournamentsQuery;
use WStrategies\BMB\Includes\Service\TournamentFilter\TournamentFilterInterface;
use WStrategies\BMB\Public\Partials\shared\BracketListItem;
use WStrategies\BMB\Public\Partials\shared\PaginationWidget;
use WStrategies\BMB\Public\Partials\TemplateInterface;
use WStrategies\BMB\Includes\Service\FilterPageService;

class TournamentsPage implements TemplateInterface {
  private DashboardTournamentsQuery $tournament_query;
  private FilterPageService $filter_service;
  private string $role;
  private static int $PER_PAGE = 5;
  private static string $DEFAULT_ROLE = 'playing';
  private static array $filter_data = [
    [
      'paged_status' => 'live',
      'label' => 'Live',
      'color' => 'green',
      'show_circle' => true,
      'fill_circle' => true,
    ],
    [
      'paged_status' => 'private',
      'label' => 'Private',
      'color' => 'blue',
      'show_circle' => true,
      'fill_circle' => true,
    ],
    [
      'paged_status' => 'upcoming',
      'label' => 'Upcoming',
      'color' => 'yellow',
      'show_circle' => true,
      'fill_circle' => true,
    ],
    [
      'paged_status' => 'complete',
      'label' => 'Complete',
      'color' => 'white',
      'show_circle' => true,
      'fill_circle' => true,
    ],
  ];

  public function __construct($args = []) {
    $this->tournament_query =
      $args['tournament_query'] ?? new DashboardTournamentsQuery();
    $this->filter_service = $args['filter_service'] ?? new FilterPageService();
  }

  private function init() {
    $role = get_query_var('role', self::$DEFAULT_ROLE);
    $this->role = $role;

    // Initialize filter service with query vars
    $this->filter_service->init();

    // Initialize filters using the service
    $this->filter_service->init_filters(
      self::$filter_data,
      [$this, 'create_filter'],
      [$this, 'get_filtered_url']
    );

    // Set active filter
    $this->filter_service->set_active_filter();
  }

  /**
   * Factory function to create filter instances
   */
  public function create_filter(array $data) {
    return new DashboardTournamentFilter([
      'tournament_query' => $this->tournament_query,
      'paged_status' => $data['paged_status'],
      'role' => $this->role,
      'per_page' => self::$PER_PAGE,
    ]);
  }

  /**
   * Generate filtered URL for a given status
   */
  public function get_filtered_url(string $status): string {
    return add_query_arg(
      ['role' => $this->role, 'status' => $status],
      get_permalink() . 'tournaments'
    );
  }

  /**
   * Generate filtered URL for role buttons (hosting/playing)
   */
  public function get_role_filtered_url(string $role, string $status): string {
    return add_query_arg(
      ['role' => $role, 'status' => $status],
      get_permalink() . 'tournaments'
    );
  }

  public function get_role_link(string $label, bool $active, string $url) {
    ob_start(); ?>
    <a class="tw-p-16 tw-text-white tw-text-20 lg:tw-text-24 tw-rounded-8 tw-flex tw-flex-1 tw-justify-center tw-font-500<?php echo $active
      ? ' tw-bg-white/20'
      : ''; ?> hover:tw-cursor-pointer"
       href="<?php echo $url; ?>"><?php echo $label; ?></a>
    <?php return ob_get_clean();
  }

  public function render(): false|string {
    $this->init();
    $brackets = [];
    $num_pages = 0;

    // get first active filter
    $active_filter = $this->filter_service->get_active_filter();
    if ($active_filter) {
      $brackets = $active_filter->get_tournaments(
        $this->filter_service->get_paged()
      );
      $num_pages = $active_filter->get_max_num_pages();
    }

    ob_start();
    ?>
      <div class="tw-flex tw-flex-col tw-gap-40">
        <div class="tw-flex tw-flex-col tw-gap-16">
          <h1 class="wpbb-dashboard-page-title tw-text-24 sm:tw-text-48 lg:tw-text-64 tw-font-700 tw-leading-none">Tournaments</h1>
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
          <div class="tw-flex tw-justify-start">
            <?php echo $this->get_role_link(
              'Playing',
              $this->role === 'playing',
              $this->get_role_filtered_url(
                'playing',
                $this->filter_service->get_paged_status()
              )
            ); ?>
            <?php echo $this->get_role_link(
              'Hosting',
              $this->role === 'hosting',
              $this->get_role_filtered_url(
                'hosting',
                $this->filter_service->get_paged_status()
              )
            ); ?>
          </div>
          <?php echo $this->filter_service->render_filter_buttons(); ?>
            <div class="tw-flex tw-flex-col tw-gap-15">
              <div id="wpbb-tournaments-modals"></div>
              <div id="wpbb-tournaments-list-container">
                <?php foreach ($brackets as $bracket) {
                  echo BracketListItem::bracket_list_item($bracket);
                } ?>
              </div>
              <?php PaginationWidget::pagination(
                $this->filter_service->get_paged(),
                $num_pages
              ); ?>
            </div>
          </div>
          <?php if (empty($brackets)): ?>
            <p class='tw-text-16 lg:tw-text-20 tw-font-500 tw-my-0 tw-text-center tw-text-white/50'>No tournaments found.</p>
          <?php endif; ?>
        </div>
      </div>
    <?php return ob_get_clean();
  }
}
