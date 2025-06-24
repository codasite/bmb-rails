<?php

namespace WStrategies\BMB\Public\Partials\BracketBoard;

use WP_Query;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Public\Partials\shared\BracketsCommon;
use WStrategies\BMB\Public\Partials\shared\BracketCards;
use WStrategies\BMB\Public\Partials\shared\PartialsContants;
use WStrategies\BMB\Public\Partials\TemplateInterface;
use WStrategies\BMB\Features\MobileApp\RequestService;
use WStrategies\BMB\Features\MobileApp\MobileAppMetaQuery;
use WStrategies\BMB\Includes\Service\SettingsService;
use WStrategies\BMB\Includes\Service\FilterPageService;
use WStrategies\BMB\Includes\Service\TournamentFilter\Public\PublicBracketFilter;
use WStrategies\BMB\Includes\Service\TournamentFilter\Public\PublicBracketsQuery;
use WStrategies\BMB\Features\Bracket\Domain\BracketQueryTypes;

class BracketBoardPage implements TemplateInterface {
  private BracketRepo $bracket_repo;
  private PlayRepo $play_repo;
  private RequestService $request_service;
  private SettingsService $settings_service;
  private PublicBracketsQuery $brackets_query;
  private FilterPageService $filter_service;
  private static int $PER_PAGE = 8;
  private static array $filter_data = [
    [
      'paged_status' => BracketQueryTypes::FILTER_LIVE,
      'label' => 'Live',
      'color' => 'green',
      'show_circle' => true,
      'fill_circle' => true,
    ],
    [
      'paged_status' => BracketQueryTypes::FILTER_UPCOMING,
      'label' => 'Upcoming',
      'color' => 'yellow',
      'show_circle' => true,
      'fill_circle' => false,
    ],
    [
      'paged_status' => BracketQueryTypes::FILTER_IN_PROGRESS,
      'label' => 'In Progress',
      'color' => 'blue',
      'show_circle' => true,
      'fill_circle' => true,
    ],
    [
      'paged_status' => BracketQueryTypes::FILTER_COMPLETED,
      'label' => 'Completed',
      'color' => 'white',
      'show_circle' => true,
      'fill_circle' => true,
    ],
  ];

  public function __construct(array $args = []) {
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
    $this->play_repo = $args['play_repo'] ?? new PlayRepo();
    $this->request_service = $args['request_service'] ?? new RequestService();
    $this->settings_service =
      $args['settings_service'] ?? new SettingsService();
    $this->brackets_query =
      $args['brackets_query'] ?? new PublicBracketsQuery();

    // Create filter service with configuration
    $this->filter_service = new FilterPageService(
      self::$filter_data,
      [$this, 'create_filter'],
      [$this, 'get_filtered_url']
    );
  }

  private function init() {
    // Initialize filter service (gets query vars, creates filters, sets active filter)
    $this->filter_service->init();
  }

  /**
   * Factory function to create filter instances
   */
  public function create_filter(array $data) {
    return new PublicBracketFilter([
      'brackets_query' => $this->brackets_query,
      'paged_status' => $data['paged_status'],
      'per_page' => self::$PER_PAGE,
    ]);
  }

  /**
   * Generate filtered URL for a given status
   */
  public function get_filtered_url(string $status): string {
    return add_query_arg('status', $status, get_permalink()) .
      '#wpbb-user-brackets';
  }

  public function render_header(): string {
    ob_start(); ?>
        <div class="wpbb-page-header tw-flex tw-flex-col tw-py-60 tw-gap-15 tw-items-center">
            <div class="logo-svg"></div>
            <h1 class="tw-text-32 sm:tw-text-48 md:tw-text-64 lg:tw-text-80 tw-font-700 tw-text-center">Bracket Board</h1>
        </div>
        <?php return ob_get_clean();
  }

  public function render_content(): string {
    $this->init();

    $featured_brackets = BracketsCommon::get_public_brackets([
      'tags' => [PartialsContants::BMB_OFFICIAL],
      'status' => PartialsContants::ALL_STATUS,
      'posts_per_page' => $this->settings_service->get_featured_brackets_count(),
    ]);

    ob_start();
    ?>
        <div class="wpbb-faded-bracket-bg tw-py-30 md:tw-py-60 tw-px-20">
            <div class="tw-flex tw-flex-col tw-max-w-[1160px] tw-m-auto">
                <!-- Featured Section -->
                <div class="tw-flex tw-flex-col tw-gap-30">
                    <h2 id="wpbb-featured-brackets" class="tw-text-36 md:tw-text-48 tw-font-700">Featured</h2>
                    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-10 md:[&>*]:tw-h-full">
                        <?php foreach (
                          $featured_brackets['brackets']
                          as $bracket
                        ):
                          echo BracketCards::vip_bracket_card($bracket);
                        endforeach; ?>
                    </div>
                </div>

                <!-- All Brackets Section -->
                <div class="tw-flex tw-flex-col">
                    <h2 id="wpbb-user-brackets" class="tw-text-36 md:tw-text-48 tw-font-700 tw-pt-60 tw-scroll-mt-5">User Brackets</h2>
                    <div class="tw-flex tw-gap-10 tw-py-24 tw-flex-wrap">
                        <?php echo $this->filter_service->render_filter_buttons(); ?>
                    </div>
                    <div class="tw-flex tw-flex-col tw-gap-15" id="wpbb-infinite-scroll-bracket-list">
                        <!-- React component will be mounted here -->
                    </div>
                </div>
            </div>
        </div>
        <?php return ob_get_clean();
  }

  public function render(): false|string {
    ob_start(); ?>
        <div class="wpbb-reset tw-bg-dd-blue">
            <div class="tw-flex tw-flex-col">
                <?php echo $this->render_header(); ?>
                <?php echo $this->render_content(); ?>
            </div>
            <div id='wpbb-public-bracket-modals'></div>
        </div>
        <?php return ob_get_clean();
  }
}
