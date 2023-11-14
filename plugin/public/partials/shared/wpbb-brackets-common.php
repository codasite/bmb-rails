<?php
require_once('wpbb-partials-common.php');
require_once('wpbb-partials-constants.php');
require_once plugin_dir_path(dirname(__FILE__, 3)) . 'includes/domain/class-wpbb-bracket.php';
require_once plugin_dir_path(dirname(__FILE__, 3)) . 'includes/repository/class-wpbb-bracket-play-repo.php';
require_once plugin_dir_path(dirname(__FILE__, 3)) . 'includes/domain/class-wpbb-bracket-play.php';

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
		'hover:tw-bg-white',
		'hover:tw-text-dark-blue',
	];
	$active_cls = [
		'tw-bg-white',
		'!tw-text-dark-blue',
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


function bracket_tag($label, $color, $filled = true) {
	$filled_path = WPBB_PLUGIN_DIR . 'public/assets/icons/ellipse.svg';
	$empty_path = WPBB_PLUGIN_DIR . 'public/assets/icons/ellipse_empty.svg';
	ob_start();
?>
	<div class="tw-text-<?php echo $color ?> tw-bg-<?php echo $color; ?>/15 tw-border tw-border-solid tw-px-8 tw-py-4 tw-flex tw-gap-4 tw-items-center tw-rounded-8">
		<?php echo $filled ? file_get_contents($filled_path) : file_get_contents(($empty_path)); ?>
		<span class="tw-font-500 tw-text-12"><?php echo $label ?></span>
	</div>
<?php
	return ob_get_clean();
}

function upcoming_bracket_tag() {
	return bracket_tag('Upcoming', 'yellow');
}

function live_bracket_tag() {
	return bracket_tag('Live', 'green');
}

function completed_bracket_tag() {
	return bracket_tag('Complete', 'yellow');
}

function scored_bracket_tag() {
	return bracket_tag('Scored', 'yellow');
}

/**
 * This button goes to the Play Bracket page
 */
function play_bracket_btn($endpoint) {
	ob_start();
?>
	<a class="tw-border-green tw-border-solid tw-border tw-bg-green/15 hover:tw-bg-green hover:tw-text-dd-blue tw-px-16 tw-py-12 tw-flex tw-justify-center sm:tw-justify-start tw-gap-10 tw-items-center tw-rounded-8 tw-text-white" href="<?php echo esc_url($endpoint) ?>">
		<?php echo file_get_contents(WPBB_PLUGIN_DIR . 'public/assets/icons/play.svg'); ?>
		<span class="tw-font-500">Play Bracket</span>
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
		'primary' => array_merge($base_cls, array('tw-border-white/50', 'tw-bg-white/15', 'tw-gap-10', 'tw-px-16', 'tw-py-12', 'hover:tw-bg-white', 'hover:tw-text-black')),
		'compact' => array_merge($base_cls, array('tw-border-white/50', 'tw-bg-white/15', 'tw-gap-4', 'sm:tw-px-8', 'sm:tw-py-4', 'hover:tw-bg-white', 'hover:tw-text-black')),
		'final' => array_merge($base_cls, array('wpbb-view-final-leaderboard-btn', 'tw-border-transparent', 'tw-bg-clip-padding', 'tw-gap-10', 'tw-px-16', 'tw-py-12')),
	);

	if ($variant === 'final') {
		$label = 'View Final Leaderboard';
		$final = true;
	}

	ob_start();
?>
	<a class="<?php echo implode(' ', $cls_list[$variant]) ?>" href="<?php echo esc_url($endpoint) ?>">
		<?php echo file_get_contents(WPBB_PLUGIN_DIR . 'public/assets/icons/trend_up.svg'); ?>
		<span class="tw-font-500 tw-text-16"><?php echo esc_html($label) ?></span>
	</a>
<?php
	$btn = ob_get_clean();
	return $final ? gradient_border_wrap($btn, array('wpbb-leaderboard-gradient-border tw-rounded-8')) : $btn;
}

function public_bracket_active_buttons(Wpbb_Bracket $bracket) {
	$bracket_play_link = get_permalink($bracket->id) . '/play';
	$leaderboard_link = get_permalink($bracket->id) . '/leaderboard';
	ob_start();
	?>
  <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-8 sm:tw-gap-16">
    <!-- This goes to the Play Bracket page -->
		<?php echo play_bracket_btn($bracket_play_link); ?>
    <!-- This goes to the Score Bracket page -->
		<?php echo view_leaderboard_btn($leaderboard_link); ?>
  </div>
<?php
	return ob_get_clean();
}

function public_bracket_completed_buttons(Wpbb_Bracket $bracket) {
	$leaderboard_link = get_permalink($bracket->id) . '/leaderboard';

	ob_start();
	?>
  <div class="tw-flex">
    <!-- This goes to the Leaderboard page -->
		<?php echo view_leaderboard_btn($leaderboard_link, 'final'); ?>
  </div>
	<?php
	return ob_get_clean();
}

function public_bracket_list_item(Wpbb_Bracket $bracket, Wpbb_BracketPlayRepo $play_repo = null) {
	$name = $bracket->title;
	$num_teams = $bracket->num_teams;
	$num_plays = $play_repo ? $play_repo->get_count([
		'bracket_id' => $bracket->id,
		'is_printed' => true,
	]) : 0;

	$id = $bracket->id;
	$completed = $bracket->status === 'complete';
  $status = $bracket->status;
  $upcoming = !$bracket->is_playable;
  $bracket_tag = live_bracket_tag();
  if ($upcoming) {
		$bracket_tag = upcoming_bracket_tag();
  } else if ($completed) {
		$bracket_tag = completed_bracket_tag();
  }

	ob_start();
?>
	<div class="tw-border-2 tw-border-solid tw-border-<?php echo $completed ? 'white/15' : 'blue' ?> tw-bg-dd-blue tw-flex tw-flex-col tw-gap-10 tw-p-30 tw-rounded-16">
		<div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between sm:tw-items-center tw-gap-8">
			<span class="tw-font-500 tw-text-12"><?php echo esc_html($num_teams) ?>-Team Bracket</span>
			<div class="tw-flex tw-gap-4 tw-items-center">
				<?php echo $bracket_tag ?>
				<?php echo file_get_contents(WPBB_PLUGIN_DIR . 'public/assets/icons/bar_chart.svg'); ?>
				<span class="tw-font-500 tw-text-20 tw-text-white"><?php echo esc_html($num_plays) ?></span>
				<span class="tw-font-500 tw-text-20 tw-text-white/50">Plays</span>
			</div>
		</div>
		<div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between tw-gap-15 md:tw-justify-start sm:tw-items-center">
			<h2 class="tw-text-white tw-font-700 tw-text-20 sm:tw-text-30"><?php echo esc_html($name) ?></h2>
		</div>
		<div class="tw-mt-10">
			<?php echo $completed ? public_bracket_completed_buttons($bracket) : public_bracket_active_buttons($bracket); ?>
		</div>
	</div>
<?php
	return ob_get_clean();
}
