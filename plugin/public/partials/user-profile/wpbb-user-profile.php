<?php
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wpbb-bracket-play-repo.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-bracket-play.php';
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wpbb-bracket-repo.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-bracket.php';
require_once WPBB_PLUGIN_DIR . 'public/partials/shared/wpbb-brackets-common.php';
require_once WPBB_PLUGIN_DIR . 'public/partials/shared/wpbb-pagination-widget.php';
require_once WPBB_PLUGIN_DIR . 'public/partials/shared/wpbb-partials-constants.php';

$bracket_repo = new Wpbb_BracketRepo();
$play_repo = new Wpbb_BracketPlayRepo();

$paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;
$paged_status = get_query_var('status');

if (empty($paged_status)) {
	$paged_status = 'all';
}

$all_statuses = ['publish', 'score', 'complete', UPCOMING_STATUS];
$active_status = ['publish'];
$scored_status = ['score', 'complete'];

if ($paged_status === 'all') {
	$status_query = $all_statuses;
} else if ($paged_status === LIVE_STATUS) {
	$status_query = $active_status;
} else if ($paged_status === UPCOMING_STATUS) {
$status_query = [UPCOMING_STATUS];
} else if ($paged_status === 'scored') {
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


// RECENT PLAY HISTORY
// PLAY CARDS

// BRACKETS
// sort buttons
// bracket cards
// pagination
?>
<div class="wpbb-reset wpbb-faded-bracket-bg tw-flex tw-flex-col tw-gap-30 tw-pt-60 tw-pb-[150px] tw-px-20">
	<div class="tw-max-w-screen-lg tw-mx-auto">
		<!-- Profile picture VIP, name -->
		<div class="tw-flex tw-gap-30 tw-py-60 tw-self-center">
		</div>
	</div>
</div>
