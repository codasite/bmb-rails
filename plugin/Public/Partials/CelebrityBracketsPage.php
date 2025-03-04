<?php
namespace WStrategies\BMB\Public\Partials;

use WP_Query;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Public\Partials\shared\BracketCards;
use WStrategies\BMB\Public\Partials\shared\PaginationWidget;
use WStrategies\BMB\Features\MobileApp\MobileAppUtils;

class CelebrityBracketsPage implements TemplateInterface {
  private int $posts_per_page = 6;
  private PlayRepo $play_repo;
  private BracketRepo $bracket_repo;
  private MobileAppUtils $mobile_app_utils;

  public function __construct(array $args = []) {
    $this->play_repo = $args['play_repo'] ?? new PlayRepo();
    $this->bracket_repo = $args['bracket_repo'] ?? new BracketRepo();
    $this->mobile_app_utils = $args['mobile_app_utils'] ?? new MobileAppUtils();
  }

  public function get_current_page(): int {
    return get_query_var('paged') ? absint(get_query_var('paged')) : 1;
  }

  public function build_query_args(int $paged): array {
    $query_args = [
      'post_type' => [Bracket::get_post_type(), Play::get_post_type()],
      'posts_per_page' => $this->posts_per_page,
      'paged' => $paged,
      'tag_slug__in' => ['bmb_vip_featured'],
    ];

    if ($this->mobile_app_utils->is_mobile_app_request()) {
      $query_args['meta_query'] = $this->get_mobile_meta_query();
    }

    return $query_args;
  }

  public function get_mobile_meta_query(): array {
    return [
      'relation' => 'OR',
      [
        'key' => 'bracket_fee',
        'value' => '0',
        'compare' => '=',
      ],
      [
        'key' => 'bracket_fee',
        'compare' => 'NOT EXISTS',
      ],
    ];
  }

  public function fetch_posts(array $query_args): array {
    $query = new WP_Query($query_args);
    return [
      'posts' => $query->posts,
      'num_pages' => $query->max_num_pages,
    ];
  }

  public function convert_post_to_entity(\WP_Post $post): ?object {
    if ($post->post_type === Bracket::get_post_type()) {
      return $this->bracket_repo->get($post);
    } elseif ($post->post_type === Play::get_post_type()) {
      return $this->play_repo->get($post);
    }
    return null;
  }

  public function get_brackets_and_plays(): array {
    $paged = $this->get_current_page();
    $query_args = $this->build_query_args($paged);
    $query_result = $this->fetch_posts($query_args);

    $brackets_and_plays = array_filter(
      array_map([$this, 'convert_post_to_entity'], $query_result['posts'])
    );

    return [
      'brackets_and_plays' => $brackets_and_plays,
      'num_pages' => $query_result['num_pages'],
    ];
  }

  public function render_header(): string {
    ob_start(); ?>
    <div class="wpbb-page-header tw-flex tw-flex-col tw-py-60 tw-gap-15 tw-items-center ">
      <div class="logo-svg"></div>
      <h1 class="tw-text-32 sm:tw-text-48 md:tw-text-64 lg:tw-text-80 tw-font-700 tw-text-center">Celebrity Brackets</h1>
    </div>
    <?php return ob_get_clean();
  }

  public function render_content(
    array $brackets_and_plays,
    int $paged,
    int $num_pages
  ): string {
    ob_start(); ?>
    <div class="wpbb-faded-bracket-bg tw-py-30 md:tw-py-60 tw-px-20 ">
      <div class="tw-flex tw-flex-col tw-gap-30 tw-max-w-[1160px] tw-m-auto ">
        <h2 class="tw-text-36 md:tw-text-48 tw-font-700 ">Featured</h2>
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-10 tw-items-stretch" style="grid-auto-rows: 1fr;">
          <?php foreach ($brackets_and_plays as $obj): ?>
            <?php echo BracketCards::vip_switcher($obj); ?>
          <?php endforeach; ?>
        </div>
        <?php PaginationWidget::pagination($paged, $num_pages); ?>
      </div>
    </div>
    <?php return ob_get_clean();
  }

  public function render(): false|string {
    $result = $this->get_brackets_and_plays();
    $paged = $this->get_current_page();

    ob_start();
    ?>
    <div class="wpbb-reset tw-bg-dd-blue">
      <div class="tw-flex tw-flex-col">
        <?php echo $this->render_header(); ?>
        <?php echo $this->render_content(
          $result['brackets_and_plays'],
          $paged,
          $result['num_pages']
        ); ?>
      </div>
      <div id='wpbb-public-bracket-modals'></div>
    </div>
    <?php return ob_get_clean();
  }
}
