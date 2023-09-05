<?php
$page = get_query_var('paged');
function wpbb_get_plays($tournament_id) {
	$args = array(
		'post_type' => 'bracket_pick',
		'posts_per_page' => -1,

		// 'meta_query' => array(
		// 	array(
		// 		'key' => 'wpbb_play_tournament',
		// 		'value' => $tournament_id,
		// 		'compare' => '='
		// 	)
		// )
	);
	$plays = get_posts($args);
	return $plays;
}
?>
<div class="wpbb-tourney-leaderboard">
	<div class="wpbb-tourney-leaderboard-header">
		<div class="wpbb-leaderboard-header-icon">
			<?php echo file_get_contents(plugins_url('../assets/icons/trophy.svg', __FILE__)); ?>
		</div>
		<h1><?php echo esc_html(get_the_title()); ?></h1>
		<button class="wpbb-score-tournament-btn">
			<div class="wpbb-score-tournament-btn-content">
				<div class="wpbb-score-tournament-btn-icon">
					<?php echo file_get_contents(plugins_url('../assets/icons/trophy.svg', __FILE__)); ?>
				</div>
				<span class="wpbb-score-tournament-btn-text">Score Tournament</span>
			</div>
		</button>
	</div>
	<div class="wpbb-tournament-plays-container">
		<h2 class="wpbb-tournament-plays-title">Plays by Tournament Participants</h2>
		<div class="wpbb-tournament-plays">
			<?php
			$plays = wpbb_get_plays(get_the_ID());
			foreach ($plays as $play) {
				// print_r($play);
				$play_id = $play->ID;
				$play_author = $play->post_author;
				$author_name = get_the_author_meta('display_name', $play_author);
				$time_ago = human_time_diff(get_the_time('U', $play_id), current_time('timestamp')) . ' ago';
				// $play_date = $play->post_date;
				// $play_date = date('F j, Y', strtotime($play_date));
				// $play_score = get_post_meta($play_id, 'wpbb_play_score', true);
				// $play_score = $play_score ? $play_score : 0;
				// $play_score = number_format($play_score);
				ob_start();
			?>
				<div class="wpbb-leaderboard-play-row">
					<div class="wpbb-leaderboard-play-winning-pick-container">
						<div class="wpbb-leaderboard-play-winning-pick">
							milwaukee
						</div>
						<div class="wpbb-leaderboard-play-winning-pick-label">
							winning team
						</div>
					</div>
					<div class="wpbb-leaderboard-play-author-info">
						<div class="wpbb-leaderboard-play-author-name">
							<?php echo esc_html($author_name); ?>
						</div>
						<div class="wpbb-leaderboard-play-date">
							<?php echo "played " . esc_html($time_ago); ?>
						</div>
					</div>
					<button class="wpbb-leaderboard-view-play-btn" href="#">
						<div class="wpbb-leaderboard-view-play-btn-content">
							<div class="wpbb-leaderboard-view-play-btn-icon">
								<?php echo file_get_contents(plugins_url('../assets/icons/arrow_up_right.svg', __FILE__)); ?>
							</div>
							<span class="wpbb-leaderboard-view-play-btn-text">View Play</span>
						</div>
					</button>
				</div>
			<?php
				echo ob_get_clean();
			}


			?>

		</div>
	</div>
</div>