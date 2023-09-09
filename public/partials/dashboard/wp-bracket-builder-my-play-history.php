<?php
require_once 'wp-bracket-builder-dashboard-common.php';
$shared_dir = plugin_dir_path(dirname(__FILE__)) . 'shared/';
require_once $shared_dir . 'wp-bracket-builder-partials-common.php';
require_once $shared_dir . 'wp-bracket-builder-tournaments-common.php';
require_once plugin_dir_path(dirname(__FILE__, 3)) . 'includes/repository/class-wp-bracket-builder-bracket-play-repo.php';

$play_repo = new Wp_Bracket_Builder_Bracket_Play_Repository();

$plays = $play_repo->get_all_by_author(get_current_user_id());

function play_list_item($play) {
	$tournament_name = $play->title;
	$user_rank = 99999;
	$complete = $play->status === 'complete';
	$play_id = $play->id;
	$tournament_id = $play->tournament_id;
	$play_link = get_permalink() . 'tournaments/' . $tournament_id . '/play/' . $play_id;
	$leaderboard_link = get_permalink() . 'tournaments/' . $tournament_id . '/leaderboard';
	$trend_up = true;
	$trend_icon = $trend_up ? 'arrow_up.svg' : 'arrow_down.svg';
	$leaderboard_variant = $complete ? 'final' : 'primary';
	$user_score_percent = 75;// $user_score * 100;
	ob_start();

	// $tournament_name = $play['tournament_name'];
	// $user_rank = number_format($play['user_rank']);
	// $user_score = $play['user_score'];
	// $complete = $play['complete'];
	// $play_id = $play['play_id'];
	// $tournament_id = $play['tournament_id'];
	// $play_link = get_permalink() . 'tournaments/' . $tournament_id . '/play/' . $play_id;
	// $leaderboard_link = get_permalink() . 'tournaments/' . $tournament_id . '/leaderboard';
	// $trend_up = $play['trend_up'];
	// $trend_icon = $trend_up ? 'arrow_up.svg' : 'arrow_down.svg';
	// $leaderboard_variant = $complete ? 'final' : 'primary';
	// $user_score_percent = $user_score * 100;
	// ob_start();
?>
	<div class="tw-flex tw-justify-between tw-p-30 tw-rounded-16 tw-border-2 tw-border-solid tw-border-blue/20 tw-bg-blue/5">
		<div class="tw-flex tw-flex-col tw-gap-20">
			<h2 class="tw-font-700 tw-text-30 tw-text-white"><?php echo esc_html($tournament_name) ?></h2>
			<div class="tw-flex tw-gap-16">
				<!-- Play this tournament again -->
				<?php echo view_play_btn($play_link); ?>
				<!-- View the leaderboard for this tournament -->
				<?php echo view_leaderboard_btn($leaderboard_link, $leaderboard_variant); ?>
			</div>
		</div>
		<div class="tw-flex tw-flex-col tw-justify-between tw-items-end">
			<div class="tw-flex tw-gap-4 tw-items-center">
				<?php echo file_get_contents(plugins_url("../../assets/icons/$trend_icon", __FILE__)); ?>
				<span class="tw-font-500 tw-text-16 tw-text-white"><?php echo esc_html($user_rank) ?></span>
				<span class="tw-font-500 tw-text-16 tw-text-white/50">Rank</span>
			</div>
			<div class="tw-flex tw-flex-col tw-items-end">
				<h2 class="tw-font-700 tw-text-48 tw-text-white"><?php echo esc_html($user_score_percent) ?>%</h2>
				<span class="tw-font-500 tw-text-12 tw-text-white">My Score</span>
			</div>
		</div>
	</div>
<?php
	return ob_get_clean();
}
?>
<div class="tw-flex tw-flex-col tw-gap-30">
	<h1>My Play History</h1>
	<div class="tw-flex tw-flex-col tw-gap-16">
		<?php foreach ($plays as $play) {
			echo play_list_item($play);
		} ?>
	</div>
</div>