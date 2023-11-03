<?php
// require_once('shared/wpbb-brackets-common.php');
require_once('shared/wpbb-brackets-common.php');
require_once('shared/wpbb-partials-common.php');
require_once plugin_dir_path(dirname(__FILE__, 2)) . 'includes/repository/class-wpbb-bracket-play-repo.php';
require_once(plugin_dir_path(dirname(__FILE__, 2)) . 'includes/repository/class-wpbb-bracket-repo.php');
require_once plugin_dir_path(dirname(__FILE__, 2)) . 'public/partials/shared/wpbb-pagination-widget.php';


$play_repo = new Wpbb_BracketPlayRepo();

$paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;
$the_query = new WP_Query([
	'post_type' => Wpbb_BracketPlay::get_post_type(),
	'posts_per_page' => 6,
	'paged' => $paged,
	'post_status' => 'publish',
	'tag' => 'bmb_vip_play'
]);

$plays = $play_repo->get_all($the_query);

$paged_plays = get_query_var('paged') ? absint(get_query_var('paged')) : 1;
$num_plays_pages = $the_query->max_num_pages;

$paged_status = get_query_var('status');

if (empty($paged_status)) {
	$paged_status = 'all';
}

$all_status = ['publish', 'score', 'complete'];
$active_status = ['publish'];
$scored_status = ['score', 'complete'];

if ($paged_status === 'all') {
	$post_status = $all_status;
} else if ($paged_status === 'active' || $paged_status === 'live') {
	$post_status = $active_status;
} else if ($paged_status === 'scored') {
	$post_status = $scored_status;
} else {
	$post_status = $all_status;
}

$bracket_repo = new Wpbb_BracketRepo();
// $brackets = $bracket_repo->get_all([]);
$brackets = $bracket_repo->get_all([
	'post_type' => Wpbb_Bracket::get_post_type(),
	'posts_per_page' => 6,
	'post_status' => $post_status,
	'tag' => 'bmb_vip_bracket'
]);

$page = get_query_var('paged');

function wpbb_bracket_sort_buttons() {
	$all_endpoint = get_permalink();
	$status = get_query_var('status');
	$live_endpoint = add_query_arg('status', LIVE_STATUS, $all_endpoint);
	$scored_endpoint = add_query_arg('status', SCORED_STATUS, $all_endpoint);
	ob_start();
?>
	<div class="tw-flex tw-gap-10 tw-py-11">
		<?php echo wpbb_sort_button('All', $all_endpoint, !($status)); ?>
		<?php echo wpbb_sort_button('Live', $live_endpoint, $status === LIVE_STATUS); ?>
		<?php echo wpbb_sort_button('Scored', $scored_endpoint, $status === SCORED_STATUS); ?>
	</div>
<?php
	return ob_get_clean();
}

function wpbb_bust_bracket_btn($endpoint) {
	ob_start();
?>
	<a class="tw-flex tw-items-center tw-text-white tw-px-16 tw-py-11 tw-rounded-8 tw-border tw-border-solid tw-justify-center tw-gap-10 tw-bg-red/20 tw-border-red" href="<?php echo esc_url($endpoint) ?>">
		<?php echo file_get_contents(WPBB_PLUGIN_DIR . 'public/assets/icons/lightning.svg'); ?>
		<span class="tw-font-700">Bust Bracket</span>
	</a>
<?php
	return ob_get_clean();
}

function wpbb_celebrity_play_list_item($play) {
	$title = $play->title;
	$id = $play->id;
	$thumbnail = get_the_post_thumbnail_url($id);
	$play_link = get_permalink($id) . 'play';
	$bust_link = get_permalink($id) . 'bust';
	ob_start();
?>
	<div class="tw-flex tw-flex-col">
		<div class="tw-bg-[url(<?php echo $thumbnail ?>)] tw-bg-center tw-bg-no-repeat tw-bg-white tw-rounded-t-16 tw-h-[324px]">
			<div class="tw-flex tw-flex-col tw-justify-end tw-flex-grow tw-px-30 tw-rounded-t-16 tw-bg-gradient-to-t tw-from-[#03073C] tw-to-[72%] tw-border-solid tw-border-white/20 tw-border-2 tw-border-y-none tw-h-full">
				<h3 class="tw-text-30 tw-text-black"><?php echo esc_html($title) ?></h3>
			</div>
		</div>
		<div class="tw-flex tw-flex-col sm:tw-flex-row md:tw-flex-col lg:tw-flex-row tw-pt-20 tw-gap-10 tw-px-30 tw-pb-30 tw-bg-dd-blue tw-bg-gradient-to-r tw-from-[#03073C]/50 tw-to-50% tw-rounded-b-16 tw-border-solid tw-border-white/20 tw-border-2 tw-border-t-none">
			<?php echo view_play_btn($play_link); ?>
			<?php echo wpbb_bust_bracket_btn($bust_link); ?>
		</div>
	</div>
<?php
	return ob_get_clean();
}

?>
<div class="wpbb-reset tw-bg-dd-blue">
	<div class="tw-flex tw-flex-col">
		<div class="tw-flex tw-flex-row md:tw-flex-row-reverse tw-py-60 tw-gap-15 tw-items-center md:tw-justify-between tw-max-w-[1160px] tw-w-full tw-m-auto tw-px-20 lg:tw-px-0">
			<div class="logo-svg"></div>
			<h1 class="tw-text-64 sm:tw-text-80 tw-font-700 tw-text-center md:tw-text-left">Celebrity Picks</h1>
		</div>
		<div class="wpbb-celeb-plays tw-py-60 tw-px-20 lg:tw-px-0">
			<div class="tw-flex tw-flex-col tw-gap-30 tw-max-w-[1160px] tw-m-auto ">
				<h2 class="tw-text-48 tw-font-700 ">Plays</h2>
				<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-10">
					<?php foreach ($plays as $play) : ?>
						<?php echo wpbb_celebrity_play_list_item($play); ?>
					<?php endforeach; ?>
				</div>
				<?php wpbb_pagination($paged_plays, $num_plays_pages); ?>
			</div>

		</div>
		<div class="tw-flex tw-flex-col tw-gap-30 tw-py-60 tw-max-w-[1160px] tw-w-full tw-m-auto tw-px-20 lg:tw-px-0">
			<h2 class="tw-text-48 tw-font-700">Brackets</h2>
			<div class="tw-flex tw-flex-col tw-gap-15">
				<?php echo wpbb_bracket_sort_buttons(); ?>
				<?php foreach ($brackets as $bracket) : ?>
					<?php echo public_bracket_list_item($bracket, $play_repo); ?>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>
