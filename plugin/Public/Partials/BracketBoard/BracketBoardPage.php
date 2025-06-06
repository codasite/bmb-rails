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

class BracketBoardPage implements TemplateInterface {
  private BracketRepo $bracket_repo;
  private PlayRepo $play_repo;
  private RequestService $request_service;

  public function __construct(array $args = []) {
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
    $this->play_repo = $args['play_repo'] ?? new PlayRepo();
    $this->request_service = $args['request_service'] ?? new RequestService();
  }

  public function get_brackets_and_plays(): array {
    $query_args = [
      'post_type' => [Bracket::get_post_type(), Play::get_post_type()],
      'posts_per_page' => -1, // Load all brackets
      'tag_slug__in' => ['bmb_vip_featured', PartialsContants::BMB_OFFICIAL],
    ];

    if ($this->request_service->is_mobile_app_request()) {
      $query_args['meta_query'] = MobileAppMetaQuery::get_mobile_meta_query();
    }

    $query = new WP_Query($query_args);

    $brackets_and_plays = array_filter(
      array_map(function ($post) {
        if ($post->post_type === Bracket::get_post_type()) {
          return $this->bracket_repo->get($post);
        } elseif ($post->post_type === Play::get_post_type()) {
          return $this->play_repo->get($post);
        }
        return null;
      }, $query->posts)
    );

    return [
      'brackets_and_plays' => $brackets_and_plays,
      'num_pages' => $query->max_num_pages,
    ];
  }

  public function render_header(): string {
    ob_start(); ?>
        <div class="wpbb-page-header tw-flex tw-flex-col tw-py-60 tw-gap-15 tw-items-center">
            <div class="logo-svg"></div>
            <h1 class="tw-text-32 sm:tw-text-48 md:tw-text-64 lg:tw-text-80 tw-font-700 tw-text-center">Bracket Board</h1>
        </div>
        <?php return ob_get_clean();
  }

  public function render_content(array $brackets_and_plays): string {
    $featured_brackets = BracketsCommon::get_public_brackets([
      'tags' => [PartialsContants::BMB_OFFICIAL],
      'status' => PartialsContants::ALL_STATUS,
    ]);

    ob_start();
    ?>
        <div class="wpbb-faded-bracket-bg tw-py-30 md:tw-py-60 tw-px-20">
            <div class="tw-flex tw-flex-col tw-max-w-[1160px] tw-m-auto">
                <!-- Featured Section -->
                <div class="tw-flex tw-flex-col tw-gap-30">
                    <h2 class="tw-text-36 md:tw-text-48 tw-font-700">Featured</h2>
                    <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-10 tw-items-stretch" style="grid-auto-rows: 1fr;">
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
                    <h2 class="tw-text-36 md:tw-text-48 tw-font-700 tw-pt-60">User Brackets</h2>
                    <div class="tw-flex tw-gap-10 tw-py-24 tw-flex-wrap">
                        <?php echo BracketsCommon::bracket_filter_buttons(); ?>
                    </div>
                    <div class="tw-flex tw-flex-col tw-gap-15">
                        <?php echo BracketsCommon::public_bracket_list(); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php return ob_get_clean();
  }

  public function render(): false|string {
    $result = $this->get_brackets_and_plays();

    ob_start();
    ?>
        <div class="wpbb-reset tw-bg-dd-blue">
            <div class="tw-flex tw-flex-col">
                <?php echo $this->render_header(); ?>
                <?php echo $this->render_content(
                  $result['brackets_and_plays']
                ); ?>
            </div>
            <div id='wpbb-public-bracket-modals'></div>
        </div>
        <?php return ob_get_clean();
  }
}
