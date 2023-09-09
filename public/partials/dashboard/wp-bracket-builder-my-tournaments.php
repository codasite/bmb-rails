<?php
require_once 'wp-bracket-builder-dashboard-common.php';
$shared_dir = plugin_dir_path(dirname(__FILE__)) . 'shared/';
require_once $shared_dir . 'wp-bracket-builder-partials-common.php';
require_once $shared_dir . 'wp-bracket-builder-tournaments-common.php';
require_once plugin_dir_path(dirname(__FILE__, 3)) . 'includes/repository/class-wp-bracket-builder-bracket-template-repo.php';
require_once plugin_dir_path(dirname(__FILE__, 3)) . 'includes/repository/class-wp-bracket-builder-bracket-play-repo.php';

$tournament_repo = new Wp_Bracket_Builder_Bracket_Tournament_Repository();
$play_repo = new Wp_Bracket_Builder_Bracket_Play_Repository();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archive_tournament_id'])) {
	if (wp_verify_nonce($_POST['archive_tournament_nonce'], 'archive_tournament_action')) {
		echo 'archive tournament';
		echo $_POST['archive_tournament_id'];
		// $tournament_repo->update($_POST['archive_tournament_id'], ['status' => 'archive']);

		$tournament = $tournament_repo->get($_POST['archive_tournament_id']);
		$tournament->status = 'archive';
		$tournament_repo->update($tournament);
	}
}

// get all of the current user's tournaments
$tournaments = $tournament_repo->get_all_by_author(get_current_user_id());

// partition appointments based on status (completed vs any other status)
$completed_tournaments = array();
$active_tournaments = array();

foreach ($tournaments as $tournament) {
	if ($tournament->status === 'complete') {
		array_push($completed_tournaments, $tournament);
	} else {
		array_push($active_tournaments, $tournament);
	}
}

function score_tournament_btn($endpoint, $tournament) {

	ob_start();
?>
	<a class="tw-border tw-border-solid tw-border-yellow tw-bg-yellow/15 tw-px-16 tw-py-12 tw-flex tw-justify-center sm:tw-justify-start tw-gap-10 tw-items-center tw-rounded-8 tw-text-white" href="<?php echo esc_url($endpoint) ?>">
		<?php echo file_get_contents(plugins_url('../../assets/icons/trophy_24.svg', __FILE__)); ?>
		<span class="tw-font-500">Score Tournament</span>
	</a>
<?php
	return ob_get_clean();
}

function active_tournament_buttons($tournament) {
	$tournament_play_link = get_permalink() . 'tournaments/' . $tournament->id . '/play';
	$tournament_score_link = get_permalink() . 'tournaments/' . $tournament->id . '/score';
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
		<?php echo view_leaderboard_btn($tournament_score_link, 'compact'); ?>
	</div>
<?php

	return ob_get_clean();
}

function completed_tournament_buttons($tournament) {
	$play_link = get_permalink() . 'tournaments/' . $tournament->id . '/play';
	$leaderboard_link = get_permalink() . 'tournaments/' . $tournament->id . '/leaderboard';
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
		<input type="hidden" name="archive_tournament_id" value="<?php echo esc_attr($tournament_id) ?>">
		<?php wp_nonce_field('archive_tournament_action', 'archive_tournament_nonce'); ?>
		<?php echo icon_btn('../../assets/icons/archive.svg', 'submit'); ?>
	</form>
<?php
	return ob_get_clean();
}

function tournament_list_item($tournament) {
	// TODO: fix play_repo->get_all_by_tournament
	// $play_repo->get_all_by_tournament($tournament->id);

	$name = $tournament->title;
	$num_teams = $tournament->bracket_template->num_teams;
	$num_plays = 999999; //count($plays);
	$id = $tournament->id;
	$completed = $tournament->status === 'complete';
	$share_link = get_permalink() . 'tournaments/' . $id . '/share';
	$delete_link = get_permalink() . 'tournaments/';
	$play_link = get_permalink() . 'tournaments/' . $id . '/play';
	$leaderboard_link = get_permalink() . 'tournaments/' . $id . '/leaderboard';
	$archive_link = get_permalink() . 'tournaments/';
	ob_start();
?>
	<div class="tw-border-2 tw-border-solid tw-border-white/15 tw-flex tw-flex-col tw-gap-10 tw-p-30 tw-rounded-16">
		<div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between sm:tw-items-center tw-gap-8">
			<span class="tw-font-500 tw-text-12"><?php echo esc_html($num_teams) ?>-Team Bracket</span>
			<div class="tw-flex tw-gap-4 tw-items-center">
				<?php echo $completed ? completed_tournament_tag() : live_tournament_tag(); ?>
				<?php echo file_get_contents(plugins_url('../../assets/icons/bar_chart.svg', __FILE__)); ?>
				<span class="tw-font-500 tw-text-20 tw-text-white"><?php echo esc_html($num_plays) ?></span>
				<span class="tw-font-500 tw-text-20 tw-text-white/50">Plays</span>
			</div>
		</div>
		<div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between tw-gap-15 md:tw-justify-start sm:tw-items-center">
			<h2 class="tw-text-white tw-font-700 tw-text-30"><?php echo esc_html($name) ?></h2>
			<div class="tw-flex tw-gap-10 tw-items-center">
				<!-- The share button should execute an AJAX request to generate a shareable link -->
				<?php echo share_tournament_btn($share_link, $id); ?>
				<!-- The duplicate button opens up the "Host a Tournamnet" modal -->
				<?php echo duplicate_bracket_btn($share_link, $id); ?>
				<?php echo archive_tournament_btn($archive_link, $id); ?>
				<!-- The delete button submits a POST request to delete the tournament after confirming with the user-->
				<?php echo delete_bracket_btn($delete_link, $id); ?>
			</div>
		</div>
		<div class="tw-mt-10">
			<?php echo $completed ? completed_tournament_buttons($tournament) : active_tournament_buttons($tournament); ?>
		</div>
	</div>
<?php
	return ob_get_clean();
}
function tournament_section($tournaments, $title) {
	ob_start();
?>
	<div class="tw-flex tw-flex-col tw-gap-15">
		<h3 class="tw-font-500 tw-text-24 tw-text-white/50"><?php echo esc_html($title) ?></h3>
		<?php foreach ($tournaments as $tournament) {
			echo tournament_list_item($tournament);
		} ?>
	</div>
<?php
	return ob_get_clean();
}
?>

<div class="tw-flex tw-flex-col tw-gap-30">
	<h1>My Tournaments</h1>
	<a href="#" class="tw-border-solid tw-border tw-border-white tw-bg-white/15 tw-flex tw-gap-16 tw-items-center tw-justify-center tw-rounded-8 tw-p-16 hover:tw-bg-white hover:tw-text-black">
		<?php echo file_get_contents(plugins_url('../../assets/icons/signal.svg', __FILE__)); ?>
		<span class="tw-font-700 tw-text-24">Create Tournament</span>
	</a>
	<?php echo tournament_section($active_tournaments, 'Active') ?>
	<?php echo tournament_section($completed_tournaments, 'Completed') ?>
</div>