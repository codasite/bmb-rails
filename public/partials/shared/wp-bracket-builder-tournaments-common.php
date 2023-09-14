<?php
require_once('wp-bracket-builder-partials-common.php');
require_once('wp-bracket-builder-partials-constants.php');

function wpbb_sort_button($label, $endpoint, $active = false) {
	$base_cls = [
		'tw-flex',
		'tw-items-center',
		'tw-justify-center',
		'tw-text-16',
		'tw-font-500',
		'tw-rounded-8',
		'tw-py-8',
		'tw-px-16',
	];

	$inactive_cls = [
		'tw-border',
		'tw-border-solid',
		'tw-border-white/50',
	];
	$active_cls = [
		'tw-bg-white',
		'tw-text-dark-blue',
	];

	$cls_list = array_merge($base_cls, $active ? $active_cls : $inactive_cls);
	ob_start();
?>
	<a class="<?php echo implode(' ', $cls_list) ?>" href="<?php echo esc_url($endpoint) ?>">
		<?php echo esc_html($label) ?>
	</a>
<?php
	return ob_get_clean();
}


function tournament_tag($label, $color) {
	ob_start();
?>
	<div class="tw-text-<?php echo $color ?> tw-bg-<?php echo $color; ?>/15 tw-border tw-border-solid tw-px-8 tw-py-4 tw-flex tw-gap-4 tw-items-center tw-rounded-8">
		<?php echo file_get_contents(plugins_url('../../assets/icons/ellipse.svg', __FILE__)); ?>
		<span class="tw-font-500 tw-text-12"><?php echo $label ?></span>
	</div>
<?php
	return ob_get_clean();
}

function live_tournament_tag() {
	return tournament_tag('Live', 'green');
}

function completed_tournament_tag() {
	return tournament_tag('Scored', 'yellow');
}



/**
 * This button goes to the Play Bracket page
 */
function play_tournament_btn($endpoint, $tournament_id) {
	ob_start();
?>
	<a class="tw-border-green tw-border-solid tw-border tw-bg-green/15 tw-px-16 tw-py-12 tw-flex tw-justify-center sm:tw-justify-start tw-gap-10 tw-items-center tw-rounded-8 tw-text-white" href="<?php echo esc_url($endpoint) ?>">
		<?php echo file_get_contents(plugins_url('../../assets/icons/play.svg', __FILE__)); ?>
		<span class="tw-font-500">Play Tournament</span>
	</a>
<?php
	return ob_get_clean();
}


/**
 * This button goes to the Leaderboard page
 */
function view_leaderboard_btn($endpoint, $variant = 'primary') {
	$label = 'View Leaderboard';
	$final = false;

	$base_cls = array('tw-flex', 'tw-justify-center', 'sm:tw-justify-start', 'tw-items-center', 'tw-text-white', 'tw-rounded-8', 'tw-border', 'tw-border-solid', 'tw-px-16', 'tw-py-12');

	$cls_list = array(
		'primary' => array_merge($base_cls, array('tw-border-white/50', 'tw-bg-white/15', 'tw-gap-10', 'tw-px-16', 'tw-py-12')),
		'compact' => array_merge($base_cls, array('tw-border-white/50', 'tw-bg-white/15', 'tw-gap-4', 'sm:tw-px-8', 'sm:tw-py-4')),
		'final' => array_merge($base_cls, array('wpbb-view-final-leaderboard-btn', 'tw-border-transparent', 'tw-bg-clip-padding', 'tw-gap-10', 'tw-px-16', 'tw-py-12')),
	);

	if ($variant === 'final') {
		$label = 'View Final Leaderboard';
		$final = true;
	}

	ob_start();
?>
	<a class="<?php echo implode(' ', $cls_list[$variant]) ?>" href="<?php echo esc_url($endpoint) ?>">
		<?php echo file_get_contents(plugins_url('../../assets/icons/trend_up.svg', __FILE__)); ?>
		<span class="tw-font-500 tw-text-16"><?php echo esc_html($label) ?></span>
	</a>
<?php
	$btn = ob_get_clean();
	return $final ? gradient_border_wrap($btn, array('wpbb-leaderboard-gradient-border tw-rounded-8')) : $btn;
}

function public_tournament_active_buttons($tournament) {
	$tournament_play_link = get_permalink($tournament->id) . '/play';
	$leaderboard_link = get_permalink($tournament->id) . '/leaderboard';
	ob_start();
?>
	<div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-8 sm:tw-gap-16">
		<!-- This goes to the Play Bracket page -->
		<?php echo play_tournament_btn($tournament_play_link, $tournament); ?>
		<!-- This goes to the Score Tournament page -->
		<?php echo view_leaderboard_btn($leaderboard_link); ?>
	</div>
<?php
	return ob_get_clean();
}

function public_tournament_completed_buttons($tournament) {
	$leaderboard_link = get_permalink($tournament->id) . '/leaderboard';

	ob_start();
?>
	<div class="tw-flex">
		<!-- This goes to the Leaderboard page -->
		<?php echo view_leaderboard_btn($leaderboard_link, 'final'); ?>
	</div>
<?php
	return ob_get_clean();
}

function public_tournament_list_item($tournament) {
	$name = $tournament['name'];
	$num_teams = $tournament['num_teams'];
	$num_plays = $tournament['num_plays'];
	$id = $tournament['id'];
	$completed = $tournament['completed'];
	ob_start();
?>
	<div class="tw-border-2 tw-border-solid tw-border-<?php echo $completed ? 'white/15' : 'blue' ?> tw-bg-dd-blue tw-flex tw-flex-col tw-gap-10 tw-p-30 tw-rounded-16">
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
		</div>
		<div class="tw-mt-10">
			<?php echo $completed ? public_tournament_completed_buttons($tournament) : public_tournament_active_buttons($tournament); ?>
		</div>
	</div>
<?php
	return ob_get_clean();
}
