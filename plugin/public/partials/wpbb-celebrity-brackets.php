<?php
// require_once('shared/wpbb-brackets-common.php');
require_once('shared/wpbb-brackets-common.php');
require_once('shared/wpbb-partials-common.php');
require_once plugin_dir_path(dirname(__FILE__, 2)) . 'includes/repository/class-wpbb-bracket-play-repo.php';
require_once(plugin_dir_path(dirname(__FILE__, 2)) . 'includes/repository/class-wpbb-bracket-repo.php');
require_once plugin_dir_path(dirname(__FILE__, 2)) . 'public/partials/shared/wpbb-pagination-widget.php';
require_once plugin_dir_path(dirname(__FILE__, 2)) . 'public/partials/wpbb-bracket-cards.php';


$play_repo = new Wpbb_BracketPlayRepo();
$bracket_repo = new Wpbb_BracketRepo();

$paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;

$query = new WP_Query([
	'post_type' => [Wpbb_Bracket::get_post_type(), Wpbb_BracketPlay::get_post_type()],
	'posts_per_page' => 6,
	'paged' => $paged,
	'tag_slug__in' => ['bmb_vip_featured'],
]);

$num_pages = $query->max_num_pages;
$posts = $query->posts;
$brackets_and_plays = [];

foreach ($posts as $post) {
	if ($post->post_type === Wpbb_Bracket::get_post_type()) {
		$brackets_and_plays[] = $bracket_repo->get($post);
	} else if ($post->post_type === Wpbb_BracketPlay::get_post_type()) {
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
				<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-10">
					<?php foreach ($brackets_and_plays as $obj) : ?>
						<?php echo wpbb_vip_switcher($obj); ?>
					<?php endforeach; ?>
				</div>
				<?php wpbb_pagination($paged, $num_pages); ?>
			</div>
		</div>
	</div>
</div>
