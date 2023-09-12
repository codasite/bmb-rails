<?php
$page = get_query_var('paged');
// This is just temporary. Don't do this
// $complete = false;
$complete = get_post_status() === 'complete';
$winner = 'Milwaukee';
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

function wpbb_score_tournament_btn($endpoint) {
	ob_start();
?>
	<a href="#" class="tw-flex tw-justify-center tw-items-center tw-text-off-black tw-gap-8 tw-py-12 tw-px-16 tw-rounded-8 tw-bg-yellow tw-font-500 tw-mt-16">
		<?php echo file_get_contents(plugins_url('../assets/icons/trophy_small.svg', __FILE__)); ?>
		<span>Score Tournament</span>
	</a>
<?php
	return ob_get_clean();
}

function wpbb_share_tournament_btn($endpoint) {
	ob_start();
?>
	<a href="#" class="tw-flex tw-justify-center tw-items-center tw-text-black tw-gap-8 tw-py-12 tw-px-16 tw-rounded-8 tw-bg-white tw-font-500 tw-mt-16">
		<?php echo file_get_contents(plugins_url('../assets/icons/share.svg', __FILE__)); ?>
		<span>Share with contestants</span>
	</a>
<?php
	return ob_get_clean();
}

function wpbb_leaderboard_play_list_item($play, $winner = false, $complete = false) {
	$play_id = $play->ID;
	$play_author = $play->post_author;
	$author_name = get_the_author_meta('display_name', $play_author);
	$time_ago = human_time_diff(get_the_time('U', $play_id), current_time('timestamp')) . ' ago';
	$score = $winner ? .95 : .25;

	ob_start();
?>
	<div class="tw-flex tw-justify-between tw-px-30<?php echo $winner ? ' tw-border-2 tw-border-solid tw-border-green tw-rounded-16 tw-py-30' : '' ?>">
		<div class="tw-flex tw-flex-col tw-gap-16">
			<?php if ($winner) : ?>
				<div class="tw-flex tw-flex-col">
					<span class="tw-text-60 tw-font-700 tw-text-green"><?php echo $score * 100; ?>%</span>
					<span class="tw-text-16 tw-font-500 tw-text-white/50">Accuracy Score</span>
				</div>
			<?php endif; ?>
			<div class="tw-flex tw-gap-<?php echo $winner ? '20' : '16' ?>">
				<div class="tw-flex tw-flex-col tw-items-center tw-gap-<?php echo $winner ? '8' : '4' ?><?php echo $winner ? ' tw-justify-between' : '' ?>">
					<span class="tw-px-16 tw-py-4 tw-bg-white tw-text-dd-blue tw-font-700 <?php echo $winner ? 'tw-text-20' : 'tw-text-16' ?>">
						milwaukee
					</span>
					<span class="tw-text-<?php echo $winner ? '16' : '12' ?> tw-font-500<?php echo $winner ? ' tw-text-white/50' : '' ?>">
						winning team
					</span>
				</div>
				<div class="tw-flex tw-flex-col tw-flex-grow">
					<span class="tw-text-<?php echo $winner ? '32' : '24' ?> tw-font-700">
						<?php echo esc_html($author_name); ?>
					</span>
					<span class="tw-text-white/50 tw-text-<?php echo $winner ? '16' : '12' ?> tw-font-500">
						<?php echo "played " . esc_html($time_ago); ?>
					</span>
				</div>
			</div>
		</div>
		<a href="#" class="tw-flex tw-justify-center tw-items-center tw-gap-4 tw-self-<?php echo $winner ? 'end' : 'center' ?> tw-text-white tw-text-16 tw-font-500 hover:tw-text-<?php echo $complete ? 'green' : 'yellow' ?>">
			<?php echo file_get_contents(plugins_url('../assets/icons/arrow_up_right.svg', __FILE__)); ?>
			<span>View Play</span>
		</a>
	</div>
<?php
	return ob_get_clean();
}

?>
<div class="wpbb-reset tw-flex tw-flex-col tw-gap-30">
	<div class="wpbb-leaderboard-header<?php echo $complete ? ' wpbb-tourney-complete tw-border-2 tw-border-solid tw-border-green' : '' ?> tw-flex tw-flex-col tw-items-start tw-rounded-16 tw-pt-[66px] tw-px-30 tw-pb-<?php echo $complete ? '30' : '[53px]' ?>">
		<?php echo file_get_contents(plugins_url('../assets/icons/trophy.svg', __FILE__)); ?>
		<h1 class="tw-mt-16 tw-mb-12">
			<?php echo $complete ? "$winner Wins" : esc_html(get_the_title()); ?>
		</h1>
		<?php if ($complete) : ?>
			<h3 class="tw-text-20 tw-font-400 ">
				<?php echo esc_html(get_the_title()); ?>
			</h3>
		<?php endif; ?>
		<?php echo $complete ? wpbb_share_tournament_btn(get_permalink()) : wpbb_score_tournament_btn(get_permalink()); ?>
	</div>
	<div class="tw-flex tw-flex-col tw-gap-16">
		<h2 class="!tw-text-white/50 tw-text-24 tw-font-500">Plays by Tournament Participants</h2>
		<div class="tw-flex tw-flex-col tw-gap-16">
			<?php
			$plays = wpbb_get_plays(get_the_ID());
			foreach ($plays as $i => $play) {
				echo wpbb_leaderboard_play_list_item($play, $i === 0 && $complete, $complete);
			}
			?>
		</div>
	</div>
</div>