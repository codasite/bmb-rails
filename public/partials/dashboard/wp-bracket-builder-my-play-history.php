<?php
$plays = array(
	array(
		"tournament_name" => "NCAA College Football 2024 hosted by Ahmad Merritt",
		"user_rank" => 67203,
		"user_score" => .30,
		"play_id" => 1,
		"tournament_id" => 1,
		"trend_up" => true,
	),
	array(
		"tournament_name" => "NCAA College Football 2024",
		"user_rank" => 1205,
		"user_score" => .75,
		"play_id" => 2,
		"tournament_id" => 2,
		"trend_up" => false,
	),
	array(
		"tournament_name" => "NCAA Womens World Series 2024",
		"user_rank" => 6432,
		"user_score" => .45,
		"play_id" => 3,
		"tournament_id" => 3,
		"trend_up" => true,
	),
);

function play_list_item($play) {
	$tournament_name = $play['tournament_name'];
	$user_rank = $play['user_rank'];
	$user_score = $play['user_score'];
	$play_id = $play['play_id'];
	$tournament_id = $play['tournament_id'];
	$play_link = get_permalink() . 'tournaments/' . $tournament_id . '/play/' . $play_id;
	$trend_up = $play['trend_up'];
	ob_start();
?>
	<div class="wpbb-play-list-item wpbb-flex wpbb-space-between wpbb-padding-30 wpbb-border-radius-16 wpbb-border-blue-20-2">
		<div class="wpbb-flex-col wpbb-gap-20">
			<h2 class="wpbb-font-weight-700 wpbb-font-size-30 wpbb-color-white"><?php echo esc_html($tournament_name) ?></h2>
			<div class="wpbb-flex wpbb-gap-16">
				<?php echo add_to_apparel_btn($play_link); ?>
				<?php echo view_leaderboard_btn($play_link); ?>
			</div>
		</div>
		<div class="wpbb-flex-col wpbb-space-between wpbb-align-end">

		</div>
	</div>
<?php
	return ob_get_clean();
}
?>
<div class="wpbb-my-play-history wpbb-flex-col wpbb-gap-30">
	<h1>My Play History</h1>
	<div class="wpbb-flex-col wpbb-gap-16">

	</div>
</div>