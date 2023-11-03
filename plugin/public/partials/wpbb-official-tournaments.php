<?php
require_once('shared/wpbb-partials-common.php');
require_once plugin_dir_path(dirname(__FILE__, 2)) . 'includes/repository/class-wpbb-bracket-play-repo.php';
require_once plugin_dir_path(dirname(__FILE__, 2)) . 'includes/domain/class-wpbb-bracket-play.php';
require_once plugin_dir_path(dirname(__FILE__, 2)) . 'includes/repository/class-wpbb-bracket-repo.php';
require_once plugin_dir_path(dirname(__FILE__, 2)) . 'includes/domain/class-wpbb-bracket.php';
require_once('shared/wpbb-partials-constants.php');
require_once('shared/wpbb-brackets-common.php');
require_once('shared/wpbb-pagination-widget.php');

$bracket_repo = new Wpbb_BracketRepo();
$play_repo = new Wpbb_BracketPlayRepo();

$paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;
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


$the_query = new WP_Query([
	'post_type' => Wpbb_Bracket::get_post_type(),
	'tag' => 'bmb_official_bracket',
	'posts_per_page' => 8,
	'paged' => $paged,
	'post_status' => $post_status,
	// 'orderby' => 'date',
	'order' => 'DESC',
]);

$num_pages = $the_query->max_num_pages;

$tournaments = $bracket_repo->get_all($the_query);

function wpbb_tournament_sort_buttons() {
	$all_endpoint = get_permalink();
	$status = get_query_var('status');
	$live_endpoint = add_query_arg('status', LIVE_STATUS, $all_endpoint);
	$scored_endpoint = add_query_arg('status', SCORED_STATUS, $all_endpoint);
	ob_start();
?>
	<div class="tw-flex tw-justify-center tw-gap-10 tw-py-11">
		<?php echo wpbb_sort_button('All', $all_endpoint, !($status)); ?>
		<?php echo wpbb_sort_button('Live', $live_endpoint, $status === LIVE_STATUS); ?>
		<?php echo wpbb_sort_button('Scored', $scored_endpoint, $status === SCORED_STATUS); ?>
	</div>
<?php
	return ob_get_clean();
}

?>
<div class="tw-bg-dd-blue tw-py-100 tw-px-20">
	<div class="wpbb-reset wpbb-official-tourneys tw-flex tw-flex-col tw-gap-30 tw-max-w-screen-lg tw-mx-auto ">
		<div class="tw-flex tw-flex-col tw-py-30 tw-gap-15 tw-items-center ">
			<div class="logo-svg"></div>
			<h1 class="tw-text-32 sm:tw-text-80 tw-font-700 tw-text-center">Official Brackets</h1>
		</div>
		<div class="tw-flex tw-flex-col tw-gap-15">
			<?php echo wpbb_tournament_sort_buttons(); ?>
			<?php foreach ($tournaments as $tournament) : ?>
				<?php echo public_bracket_list_item($tournament, $play_repo); ?>
			<?php endforeach; ?>
			<?php wpbb_pagination($paged, $num_pages); ?>
		</div>
	</div>
</div>
