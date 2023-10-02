<?php
require_once 'wp-bracket-builder-dashboard-common.php';
$shared_dir = plugin_dir_path(dirname(__FILE__)) . 'shared/';
require_once $shared_dir . 'wp-bracket-builder-partials-common.php';
require_once $shared_dir . 'wp-bracket-builder-tournaments-common.php';
require_once $shared_dir . 'wp-bracket-builder-pagination-widget.php';
require_once plugin_dir_path(dirname(__FILE__, 3)) . 'includes/repository/class-wp-bracket-builder-bracket-template-repo.php';
require_once plugin_dir_path(dirname(__FILE__, 3)) . 'includes/repository/class-wp-bracket-builder-bracket-play-repo.php';

$tournament_repo = new Wp_Bracket_Builder_Bracket_Tournament_Repository();
$play_repo = new Wp_Bracket_Builder_Bracket_Play_Repository();

$status = get_query_var('status');
if (empty($status)) {
	$status = 'publish';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archive_tournament_id'])) {
	if (wp_verify_nonce($_POST['archive_tournament_nonce'], 'archive_tournament_action')) {
		$tournament = $tournament_repo->get($_POST['archive_tournament_id']);
		$tournament->status = 'archive';
		$tournament_repo->update($tournament);
	}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_tournament_id'])) {
	if (wp_verify_nonce($_POST['delete_tournament_nonce'], 'delete_tournament_action')) {
		$tournament_repo->delete($_POST['delete_tournament_id']);
	}
}

$paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;

$the_query = new WP_Query([
	'post_type' => Wp_Bracket_Builder_Bracket_Tournament::get_post_type(),
	'author' => get_current_user_id(),
	'posts_per_page' => 6,
	'paged' => $paged,
	'post_status' => $status,
]);

$num_pages = $the_query->max_num_pages;

$tournaments = $tournament_repo->get_all($the_query, ['fetch_template' => true]);

function score_tournament_btn($endpoint, $tournament) {

	ob_start();
?>
	<a class="tw-border tw-border-solid tw-border-yellow tw-bg-yellow/15 tw-px-16 tw-py-12 tw-flex tw-justify-center sm:tw-justify-start tw-gap-10 tw-items-center tw-rounded-8 tw-text-white" href="<?php echo esc_url($endpoint) ?>">
		<?php echo file_get_contents(plugins_url('../../assets/icons/trophy_24.svg', __FILE__)); ?>
		<span class="tw-font-500">Update Results</span>
	</a>
<?php
	return ob_get_clean();
}

function active_tournament_buttons($tournament) {
	$tournament_play_link = get_permalink($tournament->id) . 'play';
	$tournament_score_link = get_permalink($tournament->id) . 'results';
	$leaderboard_link = get_permalink($tournament->id) . 'leaderboard';
	ob_start();
?>
	<div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-end sm:tw-justify-between tw-flex-wrap tw-gap-8 sm:tw-gap-16">
		<div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-8 sm:tw-gap-16">
			<!-- This goes to the Play Bracket page -->
			<?php echo play_tournament_btn($tournament_play_link, $tournament); ?>
			<!-- This goes to the Score Tournament page -->
			<?php echo score_tournament_btn($tournament_score_link, $tournament); ?>
		</div>
		<!-- This goes to the Leaderboard page -->
		<?php echo view_leaderboard_btn($leaderboard_link, 'compact'); ?>
	</div>
<?php

	return ob_get_clean();
}

function completed_tournament_buttons($tournament) {
	$play_link = get_permalink($tournament->id) . 'play';
	$leaderboard_link = get_permalink($tournament->id) . 'leaderboard';
	ob_start();
?>
	<div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between sm:tw-items-end tw-gap-8">
		<!-- This goes to the Play Bracket page -->
		<?php echo add_to_apparel_btn($play_link); ?>
		<!-- This goes to the Leaderboard page -->
		<?php echo view_leaderboard_btn($leaderboard_link, 'compact'); ?>
	</div>
<?php
	return ob_get_clean();
}

/**
 * This button sends a POST request to archive the template
 */
function archive_tournament_btn($endpoint, $tournament_id) {
	ob_start();
?>
	<form method="post" action="<?php echo esc_url($endpoint) ?>">
		<input type="hidden" name="archive_tournament_id" value="<?php echo esc_attr($tournament_id) ?>"/>
		<?php wp_nonce_field('archive_tournament_action', 'archive_tournament_nonce'); ?>
		<?php echo icon_btn('../../assets/icons/archive.svg', 'submit'); ?>
	</form>
<?php
	return ob_get_clean();
}

function archived_tournament_tag() {
	return tournament_tag('Archive', 'white/50');
}

function trash_tournament_tag() {
	return tournament_tag('Trash', 'red');
}

function get_tournament_tag($status) {
	switch ($status) {
		case 'publish':
			return live_tournament_tag();
		case 'complete':
			return completed_tournament_tag();
		case 'archive':
			return archived_tournament_tag();
		case 'trash':
			return trash_tournament_tag();
		default:
			return '';
	}
}

function tournament_list_item($tournament, Wp_Bracket_Builder_Bracket_Play_Repository $play_repo) {
	// TODO: fix play_repo->get_all_by_tournament
	// $play_repo->get_all_by_tournament($tournament->id);

	$name = $tournament->title;
	$num_teams = $tournament->bracket_template->num_teams;
	$num_plays = $play_repo ? $play_repo->get_count([
		'meta_query' => [
			[
				'key' => 'bracket_tournament_id',
				'value' => $tournament->id,
			],
		],
	]) : 0;

	$id = $tournament->id;
	$completed = $tournament->status === 'complete';
	$completed = $tournament->status === 'complete';
	$share_link = get_permalink() . 'tournaments/' . $id . '/share';
	$delete_link = get_permalink() . 'tournaments/';
	$archive_link = get_permalink() . 'tournaments/';
	ob_start();
?>
	<div class="tw-border-2 tw-border-solid tw-border-white/15 tw-flex tw-flex-col tw-gap-10 tw-p-30 tw-rounded-16">
		<div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between sm:tw-items-center tw-gap-8">
			<span class="tw-font-500 tw-text-12"><?php echo esc_html($num_teams) ?>-Team Bracket</span>
			<div class="tw-flex tw-gap-4 tw-items-center">
				<?php echo get_tournament_tag($tournament->status); ?>
				<?php echo file_get_contents(plugins_url('../../assets/icons/bar_chart.svg', __FILE__)); ?>
				<span class="tw-font-500 tw-text-20 tw-text-white"><?php echo esc_html($num_plays) ?></span>
				<span class="tw-font-500 tw-text-20 tw-text-white/50">Plays</span>
			</div>
		</div>
		<div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between tw-gap-15 md:tw-justify-start sm:tw-items-center">
			<h2 class="tw-text-white tw-font-700 tw-text-30"><?php echo esc_html($name) ?></h2>
			<div class="tw-flex tw-gap-10 tw-items-center">
				<!-- The share button should execute an AJAX request to generate a shareable link -->
				<!-- <?php echo share_tournament_btn($share_link, $id); ?> -->
				<!-- The duplicate button opens up the "Host a Tournamnet" modal -->
				<!-- <?php echo duplicate_bracket_btn($share_link, $id); ?> -->
				<?php echo archive_tournament_btn($archive_link, $id); ?>
				<!-- The delete button submits a POST request to delete the tournament after confirming with the user-->
				<?php echo delete_post_btn($delete_link, $id, 'delete_tournament_id', 'delete_tournament_action', 'delete_tournament_nonce'); ?>
			</div>
		</div>
		<div class="tw-mt-10">
			<?php echo $completed ? completed_tournament_buttons($tournament) : active_tournament_buttons($tournament); ?>
		</div>
	</div>
<?php
	return ob_get_clean();
}

?>

<div class="tw-flex tw-flex-col tw-gap-15">
	<h1 class="tw-mb-8">My Tournaments</h1>
	<div id="wpbb-create-tournament-button-and-modal"></div>
	<div class="tw-flex tw-gap-10 tw-gap-10 tw-py-11">
		<!-- <?php echo wpbb_sort_button('All', get_permalink() . "tournaments/", $status === null); ?> -->
		<?php echo wpbb_sort_button('Live', get_permalink() . "tournaments/?status=publish", $status === 'publish'); ?>
		<?php echo wpbb_sort_button('Scored', get_permalink() . "tournaments/?status=complete", $status === 'complete'); ?>
		<?php echo wpbb_sort_button('Archive', get_permalink() . "tournaments/?status=archive", $status === 'archive'); ?>
		<?php echo wpbb_sort_button('Trash', get_permalink() . "tournaments/?status=trash", $status === 'trash'); ?>
	</div>
	<div class="tw-flex tw-flex-col tw-gap-15">
		<?php foreach ($tournaments as $tournament) {
			echo tournament_list_item($tournament, $play_repo);
		} ?>
		<?php wpbb_pagination($paged, $num_pages); ?>
	</div>
</div>
