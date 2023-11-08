<?php
// require_once('shared/wpbb-brackets-common.php');
require_once('shared/wpbb-brackets-common.php');
require_once('shared/wpbb-partials-common.php');
require_once plugin_dir_path(dirname(__FILE__, 2)) . 'includes/repository/class-wpbb-bracket-play-repo.php';
require_once(plugin_dir_path(dirname(__FILE__, 2)) . 'includes/repository/class-wpbb-bracket-repo.php');
require_once plugin_dir_path(dirname(__FILE__, 2)) . 'public/partials/shared/wpbb-pagination-widget.php';


$play_repo = new Wpbb_BracketPlayRepo();
$bracket_repo = new Wpbb_BracketRepo();

$paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;

$query = new WP_Query([
	'post_type' => [Wpbb_Bracket::get_post_type(), Wpbb_BracketPlay::get_post_type()],
	'posts_per_page' => 6,
	'paged' => $paged,
	'tag_slug__in' => ['bmb_vip_bracket', 'bmb_vip_play'],
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

function wpbb_vip_switcher($bracket_or_play) {
	if ($bracket_or_play instanceof Wpbb_Bracket) {
		return wpbb_vip_bracket_card($bracket_or_play);
	} else if ($bracket_or_play instanceof Wpbb_BracketPlay) {
		return wpbb_vip_play_card($bracket_or_play);
	}
}

function wpbb_vip_play_card($play) {
	$title = $play->title;
	$id = $play->id;
	$thumbnail = get_the_post_thumbnail_url($id);
	$play_link = get_permalink($id) . 'play';
	$leaderboard_link = get_permalink($play->bracket_id) . 'leaderboard';
	$buttons = [
		view_play_btn($play_link),
		view_leaderboard_btn($leaderboard_link),
	];
	return wpbb_vip_card($title, $thumbnail, $buttons);
}

function wpbb_vip_bracket_card($bracket) {
	$title = $bracket->title;
	$id = $bracket->id;
	$thumbnail = get_the_post_thumbnail_url($id);
	$play_link = get_permalink($id) . 'play';
	$leaderboard_link = get_permalink($id) . 'leaderboard';
	$buttons = [
		play_bracket_btn($play_link),
		view_leaderboard_btn($leaderboard_link),
	];
	return wpbb_vip_card($title, $thumbnail, $buttons);
}

function wpbb_vip_card($title, $thumbnail, array $buttons = []) {
	ob_start();
	?>
	<div class="tw-flex tw-flex-col">
		<div class="tw-bg-[url(<?php echo $thumbnail ?>)] tw-bg-center tw-bg-no-repeat tw-bg-white tw-rounded-t-16 tw-h-[324px]">
			<div class="tw-flex tw-flex-col tw-justify-end tw-flex-grow tw-px-30 tw-rounded-t-16 tw-bg-gradient-to-t tw-from-[#03073C] tw-to-[72%] tw-border-solid tw-border-white/20 tw-border-2 tw-border-y-none tw-h-full">
				<h3 class="tw-text-30 tw-text-black"><?php echo esc_html($title) ?></h3>
			</div>
		</div>
		<div class="tw-flex tw-flex-col sm:tw-flex-row md:tw-flex-col lg:tw-flex-row tw-pt-20 tw-gap-10 tw-px-30 tw-pb-30 tw-bg-dd-blue tw-bg-gradient-to-r tw-from-[#03073C]/50 tw-to-50% tw-rounded-b-16 tw-border-solid tw-border-white/20 tw-border-2 tw-border-t-none">
			<?php 
				foreach ($buttons as $button) {
					echo $button;
				}
			?>
		</div>
	</div>
	<?php
	return ob_get_clean();
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
