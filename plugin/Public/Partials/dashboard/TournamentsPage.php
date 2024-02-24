<?php

namespace WStrategies\BMB\Public\Partials\dashboard;

use WStrategies\BMB\Includes\Service\TournamentFilter\Dashboard\DashboardTournamentFilter;
use WStrategies\BMB\Includes\Service\TournamentFilter\Dashboard\DashboardTournamentsQuery;
use WStrategies\BMB\Includes\Service\TournamentFilter\TournamentFilterInterface;
use WStrategies\BMB\Public\Partials\shared\BracketListItem;
use WStrategies\BMB\Public\Partials\shared\FilterButton;
use WStrategies\BMB\Public\Partials\shared\PaginationWidget;

class TournamentsPage {
  private DashboardTournamentsQuery $tournament_query;
  private int $paged;
  private string $role;
  private string $paged_status;
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
  /**
   * @var array<FilterButton>
   */
  private array $filter_buttons = [];
  /**
   * @var array<TournamentFilterInterface>
   */
  private array $filters = [];

  public function __construct($args = []) {
    $this->tournament_query =
      $args['tournament_query'] ?? new DashboardTournamentsQuery();
  }

  private function init() {
    $this->paged = (int) get_query_var('paged')
      ? absint(get_query_var('paged'))
      : 1;
    $role = get_query_var('role', self::$DEFAULT_ROLE);
    $paged_status = get_query_var('status');
    $this->role = $role;
    $this->paged_status = $paged_status;
    $this->init_filters();
    $this->set_active_filter();
  }

  private function init_filters() {
    // Construct filters and filter buttons
    foreach (self::$filter_data as $data) {
      $filter = new DashboardTournamentFilter([
        'tournament_query' => $this->tournament_query,
        'paged_status' => $data['paged_status'],
        'role' => $this->role,
        'per_page' => self::$PER_PAGE,
      ]);
      $this->filters[] = $filter;
      $this->filter_buttons[] = new FilterButton([
        'tournament_filter' => $filter,
        'label' => $data['label'],
        'color' => $data['color'],
        'show_circle' => $data['show_circle'],
        'fill_circle' => $data['fill_circle'],
        'url' => $this->get_filtered_url($this->role, $data['paged_status']),
      ]);
    }
  }

  private function set_active_filter() {
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

  private function get_filter_by_status(string $paged_status) {
    foreach ($this->filters as $filter) {
      if ($filter->get_paged_status() === $paged_status) {
        return $filter;
      }
    }
    return null;
  }

  public function get_role_link(string $label, bool $active, string $url) {
    ob_start(); ?>
    <a class="tw-text-white tw-text-20 tw-font-500<?php echo $active
      ? ' tw-underline'
      : ' tw-opacity-50'; ?> hover:tw-cursor-pointer"
       href="<?php echo $url; ?>"><?php echo $label; ?></a>
    <?php return ob_get_clean();
  }

  public function render_filter_buttons() {
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

  public function get_filtered_url(string $role, string $status) {
    return get_permalink() . 'tournaments?role=' . $role . '&status=' . $status;
  }

  public function render() {
    $this->init();
    $brackets = [];
    $num_pages = 0;

    // get first active filter
    foreach ($this->filters as $filter) {
      if ($filter->is_active()) {
        $brackets = $filter->get_tournaments($this->paged);
        $num_pages = $filter->get_max_num_pages();
        break;
      }
    }

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
          <div class="tw-flex tw-justify-start tw-gap-16">
            <?php echo $this->get_role_link(
              'Playing',
              $this->role === 'playing',
              $this->get_filtered_url('playing', $this->paged_status)
            ); ?>
            <?php echo $this->get_role_link(
              'Hosting',
              $this->role === 'hosting',
              $this->get_filtered_url('hosting', $this->paged_status)
            ); ?>
          </div>
          <?php echo $this->render_filter_buttons(); ?>
          <div class="tw-flex tw-flex-col tw-gap-15">
            <?php foreach ($brackets as $bracket) {
              echo BracketListItem::bracket_list_item($bracket);
            } ?>
            <?php PaginationWidget::pagination($this->paged, $num_pages); ?>
          </div>
          <?php if (empty($brackets)): ?>
            <p class='tw-text-24 tw-font-500 tw-my-0'>No tournaments found.</p>
          <?php endif; ?>
        </div>
      </div>
    <?php return ob_get_clean();
  }
}
