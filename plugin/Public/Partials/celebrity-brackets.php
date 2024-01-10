<?php
namespace WStrategies\BMB\Public\Partials;

use WP_Query;
use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Public\Partials\shared\BracketCards;
use WStrategies\BMB\Public\Partials\shared\PaginationWidget;

$play_repo = new PlayRepo();
$bracket_repo = new BracketRepo();

$paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;

$query = new WP_Query([
	'post_type' => [Bracket::get_post_type(), BracketPlay::get_post_type()],
	'posts_per_page' => 6,
	'paged' => $paged,
	'tag_slug__in' => ['bmb_vip_featured'],
]);

$num_pages = $query->max_num_pages;
$posts = $query->posts;
$brackets_and_plays = [];

foreach ($posts as $post) {
	if ($post->post_type === Bracket::get_post_type()) {
		$brackets_and_plays[] = $bracket_repo->get($post);
	} else if ($post->post_type === BracketPlay::get_post_type()) {
		$brackets_and_plays[] = $play_repo->get($post);
	}
}
?>
<div class="wpbb-reset tw-bg-dd-blue">
	<div class="tw-flex tw-flex-col">
		<div class="tw-flex tw-flex-col tw-py-60 tw-gap-15 tw-items-center ">
			<div class="logo-svg"></div>
			<h1 class="tw-text-32 sm:tw-text-48 md:tw-text-64 lg:tw-text-80 tw-font-700 tw-text-center">Celebrity Brackets</h1>
		</div>
		<div class="wpbb-faded-bracket-bg tw-py-30 md:tw-py-60 tw-px-20 ">
			<div class="tw-flex tw-flex-col tw-gap-30 tw-max-w-[1160px] tw-m-auto ">
				<h2 class="tw-text-36 md:tw-text-48 tw-font-700 ">Featured</h2>
				<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-10 tw-items-stretch" style="grid-auto-rows: 1fr;">
					<?php foreach ($brackets_and_plays as $obj) : ?>
						<?php echo BracketCards::vip_switcher( $obj ); ?>
					<?php endforeach; ?>
				</div>
				<?php PaginationWidget::pagination( $paged, $num_pages ); ?>
			</div>
		</div>
	</div>
  <div id='wpbb-public-bracket-modals'></div>
</div>
