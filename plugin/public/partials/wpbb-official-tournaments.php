<?php
require_once plugin_dir_path(dirname(__FILE__, 2)) . 'includes/repository/class-wpbb-bracket-play-repo.php';
require_once plugin_dir_path(dirname(__FILE__, 2)) . 'includes/domain/class-wpbb-bracket-play.php';
require_once plugin_dir_path(dirname(__FILE__, 2)) . 'includes/repository/class-wpbb-bracket-tournament-repo.php';
require_once plugin_dir_path(dirname(__FILE__, 2)) . 'includes/domain/class-wpbb-bracket-tournament.php';
require_once('shared/wpbb-partials-constants.php');
require_once('shared/wpbb-tournaments-common.php');
require_once('shared/wpbb-pagination-widget.php');

$tournament_repo = new Wpbb_BracketTournamentRepo();
$play_repo = new Wpbb_BracketPlayRepo();

$status = get_query_var('status');

$filter_status = 'publish';
if ($status === ALL_STATUS) {
	$filter_status = 'any';
} else if ($status === SCORED_STATUS) {
	$filter_status = 'complete';
}

$paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;

$the_query = new WP_Query([
	'post_type' => Wpbb_BracketTournament::get_post_type(),
	'tag' => 'bmb_official_tourney',
	'posts_per_page' => 8,
	'paged' => $paged,
	'post_status' => $filter_status,
	// 'orderby' => 'date',
	'order' => 'DESC',
]);

$num_pages = $the_query->max_num_pages;

$tournaments = $tournament_repo->get_all($the_query);

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
<div class="wpbb-reset wpbb-official-tourneys tw-flex tw-flex-col tw-gap-30">
	<div class="tw-flex tw-flex-col tw-py-30 tw-gap-15 tw-items-center">
		<?php echo file_get_contents(WPBB_PLUGIN_DIR . 'public/assets/icons/logo_dark.svg'); ?>
		<h1 class="tw-text-80 tw-font-700 tw-text-center">Official Tournaments</h1>
	</div>
	<div class="tw-flex tw-flex-col tw-gap-15">
		<?php echo wpbb_tournament_sort_buttons(); ?>
		<?php foreach ($tournaments as $tournament) : ?>
			<?php echo public_tournament_list_item($tournament, $play_repo); ?>
		<?php endforeach; ?>
		<?php wpbb_pagination($paged, $num_pages); ?>
	</div>
</div>
