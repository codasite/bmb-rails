<?php
require_once 'wp-bracket-builder-dashboard-common.php';
$shared_dir = plugin_dir_path(dirname(__FILE__)) . 'shared/';
require_once $shared_dir . 'wp-bracket-builder-partials-common.php';
require_once $shared_dir . 'wp-bracket-builder-tournaments-common.php';

$plays = array(
	array(
		"tournament_name" => "NCAA College Football 2024 hosted by Ahmad Merritt",
		"user_rank" => 67203,
		"user_score" => .30,
		"play_id" => 1,
		"tournament_id" => 1,
		"trend_up" => true,
		"complete" => false,
	),
	array(
		"tournament_name" => "NCAA College Football 2024",
		"user_rank" => 1205,
		"user_score" => .75,
		"play_id" => 2,
		"tournament_id" => 2,
		"trend_up" => false,
		"complete" => true,
	),
	array(
		"tournament_name" => "NCAA Womens World Series 2024",
		"user_rank" => 6432,
		"user_score" => .45,
		"play_id" => 3,
		"tournament_id" => 3,
		"trend_up" => true,
		"complete" => true,
	),
);

function play_list_item($play) {
	$tournament_name = $play['tournament_name'];
	$user_rank = number_format($play['user_rank']);
	$user_score = $play['user_score'];
	$complete = $play['complete'];
	$play_id = $play['play_id'];
	$tournament_id = $play['tournament_id'];
	$play_link = get_permalink() . 'tournaments/' . $tournament_id . '/play/' . $play_id;
	$leaderboard_link = get_permalink() . 'tournaments/' . $tournament_id . '/leaderboard';
	$trend_up = $play['trend_up'];
	$trend_icon = $trend_up ? 'arrow_up.svg' : 'arrow_down.svg';
	$leaderboard_variant = $complete ? 'final' : 'primary';
	$user_score_percent = $user_score * 100;
	ob_start();
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