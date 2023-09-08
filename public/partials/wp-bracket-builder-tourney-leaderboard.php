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
<div class="wpbb-reset tw-flex tw-flex-col tw-gap-30">
	<div class="wpbb-leaderboard-header tw-flex tw-flex-col tw-items-start tw-rounded-16 tw-pt-[66px] tw-px-30 tw-pb-[53px]">
		<?php echo file_get_contents(plugins_url('../assets/icons/trophy.svg', __FILE__)); ?>
		<h1 class="tw-mt-16 tw-mb-24"><?php echo esc_html(get_the_title()); ?></h1>
		<a href="#" class="tw-flex tw-justify-center tw-items-center tw-text-off-black tw-gap-8 tw-uppercase tw-py-12 tw-px-16 tw-rounded-8 tw-bg-yellow tw-font-500">
			<!-- <div class="wpbb-score-tournament-btn-content"> -->
			<?php echo file_get_contents(plugins_url('../assets/icons/trophy_small.svg', __FILE__)); ?>
			<span>Score Tournament</span>
		</a>
	</div>
	<!-- <div> -->
	<div class="tw-flex tw-flex-col tw-gap-16">
		<h2 class="!tw-text-white/50 tw-text-24 tw-font-500">Plays by Tournament Participants</h2>
		<div class="tw-flex tw-flex-col tw-px-30 tw-gap-16">
			<?php
			$plays = wpbb_get_plays(get_the_ID());
			foreach ($plays as $play) :
				$play_id = $play->ID;
				$play_author = $play->post_author;
				$author_name = get_the_author_meta('display_name', $play_author);
				$time_ago = human_time_diff(get_the_time('U', $play_id), current_time('timestamp')) . ' ago';
			?>
				<div class="tw-flex tw-gap-16">
					<div class="tw-flex tw-flex-col tw-items-center tw-gap-4">
						<!-- <div class="wpbb-leaderboard-play-winning-pick"> -->
						<span class="tw-px-16 tw-py-4 tw-bg-white tw-text-dd-blue tw-font-700">
							milwaukee
						</span>
						<span class="tw-text-12 tw-font-500">
							winning team
						</span>
					</div>
					<div class="tw-flex tw-flex-col tw-flex-grow">
						<span class="tw-text-24 tw-font-700">
							<?php echo esc_html($author_name); ?>
						</span>
						<span class="tw-text-white/50 tw-text-12 tw-font-500">
							<?php echo "played " . esc_html($time_ago); ?>
						</span>
					</div>
					<a href="#" class="tw-flex tw-justify-center tw-items-center tw-gap-4 tw-self-center tw-text-white tw-text-16 tw-font-500 tw-py-8 tw-pl-12 tw-pr-16 tw-rounded-8 hover:tw-text-dd-blue hover:tw-bg-white">
						<?php echo file_get_contents(plugins_url('../assets/icons/arrow_up_right.svg', __FILE__)); ?>
						<span>View Play</span>
					</a>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>