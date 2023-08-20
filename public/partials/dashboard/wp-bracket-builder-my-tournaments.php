<?php
require_once 'wp-bracket-builder-common.php';

$active_tournaments = array(
	array(
		"name" => "Lakeside High Football",
		"id" => 1,
		"num_teams" => 16,
		"completed" => false,
	),
);

$completed_tournaments = array(
	array(
		"name" => "College Basketball",
		"id" => 2,
		"num_teams" => 6,
		"completed" => true,
	),
	array(
		"name" => "Midwest Baseball",
		"id" => 3,
		"num_teams" => 8,
		"completed" => true,
	),
);


function score_tournament_btn($endpoint, $tournament) {
	ob_start();
?>
	<a class="wpbb-bracket-action-btn wpbb-dashboard-score-tournament-btn wpbb-flex wpbb-gap-10 wpbb-align-center wpbb-border-radius-8 wpbb-color-white" href="<?php echo esc_url($endpoint) ?>">
		<?php echo file_get_contents(plugins_url('../../assets/icons/trophy_24.svg', __FILE__)); ?>
		<span class="wpbb-font-weight-500">Score Tournament</span>
	</a>
<?php
	return ob_get_clean();
}


function active_tournament_buttons($tournament) {
	$tournament_play_link = get_permalink() . 'tournaments/' . $tournament['id'] . '/play';
	$tournament_score_link = get_permalink() . 'tournaments/' . $tournament['id'] . '/score';
	ob_start();
?>
	<div class="wpbb-flex wpbb-gap-16">
		<?php echo play_tournament_btn($tournament_play_link, $tournament); ?>
		<?php echo score_tournament_btn($tournament_score_link, $tournament); ?>
	</div>
<?php

	return ob_get_clean();
}

function tournament_list_item($tournament) {
	$name = $tournament['name'];
	$num_teams = $tournament['num_teams'];
	$id = $tournament['id'];
	$completed = $tournament['completed'];
	$share_link = get_permalink() . 'tournaments/' . $id . '/share';
	$delete_link = get_permalink() . 'tournaments/';
	$play_link = get_permalink() . 'tournaments/' . $id . '/play';
	ob_start();
?>
	<div class="wpbb-tournament-list-item wpbb-border-grey-15-2 wpbb-flex wpbb-space-between wpbb-padding-30 wpbb-border-radius-16">
		<div class="wpbb-flex-col wpbb-gap-10 wpbb-align-start">
			<span class="wpbb-font-weight-500 wpbb-font-size-12"><?php echo esc_html($num_teams) ?>-Team Bracket</span>
			<div class="wpbb-flex wpbb-gap-10 wpbb-align-center">
				<h2 class="wpbb-color-white wpbb-font-weight-700 wpbb-font-size-30"><?php echo esc_html($name) ?></h2>
				<?php echo share_tournament_btn($share_link, $id); ?>
				<?php echo duplicate_bracket_btn($share_link, $id); ?>
				<?php echo delete_bracket_btn($delete_link, $id); ?>
			</div>
			<?php echo $completed ? add_apparel_btn($play_link) : active_tournament_buttons($tournament); ?>
		</div>
		<div class="wpbb-flex-col wpbb-space-between wpbb-align-end">
		</div>
	</div>
<?php
	return ob_get_clean();
}

function tournament_section($tournaments, $title) {
	ob_start();
?>
	<div class="wpbb-tournaments-list wpbb-flex-col wpbb-gap-15">
		<h3 class="wpbb-font-weight-500 wpbb-font-size-24 wpbb-color-grey-50"><?php echo esc_html($title) ?></h3>
		<?php foreach ($tournaments as $tournament) {
			echo tournament_list_item($tournament);
		} ?>
	</div>
<?php
	return ob_get_clean();
}

?>
<div class="wpbb-my-tournaments wpbb-flex-col wpbb-gap-30">
	<h1>My Tournaments</h1>
	<a href="#" class="wpbb-create-tournament-link wpbb-block wpbb-flex wpbb-gap-16 wpbb-align-center wpbb-justify-center wpbb-border-radius-8 wpbb-padding-16">
		<?php echo file_get_contents(plugins_url('../../assets/icons/signal.svg', __FILE__)); ?>
		<span class="wpbb-font-weight-700 wpbb-font-size-24">Create Tournament</span>
	</a>
	<?php echo tournament_section($active_tournaments, 'Active') ?>
	<?php echo tournament_section($completed_tournaments, 'Completed') ?>
</div>