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
$status_filter = get_query_var('status');

if (empty($status_filter)) {
	$status_filter = 'all';
}

$all_statuses = ['publish', 'score', 'complete', UPCOMING_STATUS];
$active_status = ['publish'];
$scored_status = ['score', 'complete'];

if ($status_filter === 'all') {
	$status_query = $all_statuses;
} else if ($status_filter === LIVE_STATUS) {
	$status_query = $active_status;
} else if ($status_filter === UPCOMING_STATUS) {
$status_query = [UPCOMING_STATUS];
} else if ($status_filter === 'scored') {
	$status_query = $scored_status;
} else {
	$status_query = $all_statuses;
}


$the_query = new WP_Query([
	'post_type' => Wpbb_Bracket::get_post_type(),
	'tag_slug__and' => ['bmb_official_bracket'],
	'posts_per_page' => 8,
	'paged' => $paged,
	'post_status' => $status_query,
	// 'orderby' => 'date',
	'order' => 'DESC',
]);

$num_pages = $the_query->max_num_pages;

$brackets = $bracket_repo->get_all($the_query);


?>
<div class="wpbb-faded-bracket-bg tw-py-20 sm:tw-py-60 tw-px-20">
	<div class="wpbb-reset wpbb-official-brackets tw-flex tw-flex-col tw-gap-30 tw-max-w-screen-lg tw-mx-auto ">
		<div class="tw-flex tw-flex-col tw-py-20 sm:tw-py-30 tw-gap-15 tw-items-center ">
			<div class="logo-svg"></div>
			<h1 class="tw-text-32 sm:tw-text-48 md:tw-text-64 lg:tw-text-80 tw-font-700 tw-text-center">Official Brackets</h1>
		</div>
		<div class="tw-flex tw-flex-col tw-gap-15">
      <div class="tw-flex tw-justify-center tw-gap-10 tw-py-11">
        <?php echo wpbb_bracket_sort_buttons(); ?>
      </div>
      <?php echo public_bracket_list(); ?>
		</div>
	</div>
</div>
