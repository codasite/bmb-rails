<?php
require_once plugin_dir_path(dirname(__FILE__, 2)) . 'includes/repository/class-wpbb-bracket-play-repo.php';
require_once plugin_dir_path(dirname(__FILE__, 2)) . 'includes/domain/class-wpbb-bracket-play.php';
require_once plugin_dir_path(dirname(__FILE__, 2)) . 'includes/repository/class-wpbb-bracket-repo.php';
require_once plugin_dir_path(dirname(__FILE__, 2)) . 'includes/domain/class-wpbb-bracket.php';

$page = get_query_var('paged');
$play_repo = new Wpbb_BracketPlayRepo();
$bracket_repo = new Wpbb_BracketRepo();
$bracket = $bracket_repo->get(get_the_ID());
$bracket_winner = $bracket->get_winning_team();
$post_status = get_post_status();
$complete = $post_status === 'complete';
$scored = $post_status === 'score';
$show_scores = $complete || $scored;

$query = [
		'post_status' => 'publish',
		'bracket_id' => $bracket->id,
		// 'is_printed' => true,
		'orderby' => 'accuracy_score',
		'order' => 'DESC',
	];

if ($bracket->results_first_updated_at) {
	$query['date_query'] = [
		[
			'column' => 'post_modified_gmt',
			'before' => $bracket->results_first_updated_at->format('Y-m-d H:i:s'),
		]
	];
}
$plays = $play_repo->get_all(
	$query,
	[
		'fetch_picks' => true,
	]
);

function wpbb_score_bracket_btn($endpoint) {
	ob_start();
?>
	<a href="<?php echo $endpoint; ?>" class="tw-flex tw-justify-center tw-items-center !tw-text-off-black tw-gap-8 tw-py-12 tw-px-16 tw-rounded-8 tw-bg-yellow tw-font-500 tw-mt-16">
		<?php echo file_get_contents(WPBB_PLUGIN_DIR . 'public/assets/icons/trophy_small.svg'); ?>
		<span>Score Bracket</span>
	</a>
<?php
	return ob_get_clean();
}

function wpbb_share_bracket_btn($endpoint) {
	ob_start();
?>
	<a href="#" class="tw-flex tw-justify-center tw-items-center tw-text-black tw-gap-8 tw-py-12 tw-px-16 tw-rounded-8 tw-bg-white tw-font-500 tw-mt-16">
		<?php echo file_get_contents(WPBB_PLUGIN_DIR . 'public/assets/icons/share.svg'); ?>
		<span>Share with contestants</span>
	</a>
<?php
	return ob_get_clean();
}

function wpbb_leaderboard_play_list_item(Wpbb_BracketPlay $play, $winner = false, $show_score = false, $complete = false) {
	$play_id = $play->id;
	$play_author = $play->author;
	$author_name = get_the_author_meta('display_name', $play_author);
	$time_ago = human_time_diff(get_the_time('U', $play_id), current_time('timestamp')) . ' ago';
	$winning_team = $play->get_winning_team();
	$winning_team_name = $winning_team ? $winning_team->name : '';
	$score = $play->accuracy_score;
	$winner = $winner && $show_score;
	$view_play_link = get_permalink($play_id);

	ob_start();
?>
	<div class="tw-flex tw-justify-between sm:tw-px-30<?php echo $winner ? ' tw-border-2 tw-border-solid tw-border-green tw-rounded-16 tw-py-30' : '' ?>">
		<div class="tw-flex tw-flex-col tw-gap-16">
			<?php if ($show_score) : ?>
				<div class="tw-flex tw-flex-col">
					<?php if ($winner) : ?>
						<span class="tw-text-60 tw-font-700 tw-text-green"><?php echo round($score * 100); ?>%</span>
						<span class="tw-text-16 tw-font-500 tw-text-white/50">Accuracy Score</span>
					<?php else : ?>
						<span class="tw-text-32 tw-font-700 tw-text-white/50"><?php echo round($score * 100); ?>%</span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="tw-flex tw-gap-<?php echo $winner ? '20' : '16' ?>">
				<div class="tw-flex tw-flex-col tw-gap-<?php echo $winner ? '8' : '4' ?><?php echo $winner ? ' tw-justify-between' : '' ?>">
				<div class="wpbb-lb-winning-team-name-container tw-px-8 tw-text-center tw-py-4 tw-bg-white tw-text-dd-blue tw-font-700 <?php echo $winner ? 'tw-text-20' : 'tw-text-16' ?>" data-team-name="<?php echo esc_html($winning_team_name)?>" data-target-width="<?php echo 115 ?>">
				</div>
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
		<a href="<?php echo $view_play_link ?>" class="tw-flex tw-justify-center tw-items-center tw-gap-4 tw-self-<?php echo $winner ? 'end' : 'center' ?> tw-text-white tw-text-16 tw-font-500 hover:tw-text-<?php echo $complete ? 'green' : 'yellow' ?>">
			<?php echo file_get_contents(WPBB_PLUGIN_DIR . 'public/assets/icons/arrow_up_right.svg'); ?>
			<span class="tw-hidden sm:tw-inline">View Play</span>
		</a>
	</div>
<?php
	return ob_get_clean();
}

?>
<div class="wpbb-reset tw-bg-dd-blue tw-flex tw-justify-center">
	<div class="tw-max-w-screen-lg tw-flex tw-flex-grow tw-flex-col tw-gap-30 tw-px-20 lg:tw-px-0 tw-py-60">

		<div class="wpbb-leaderboard-header<?php echo $complete ? ' wpbb-tourney-complete tw-border-2 tw-border-solid tw-border-green' : '' ?> tw-flex tw-flex-col tw-items-start tw-rounded-16 tw-pt-[66px] tw-px-30 tw-pb-<?php echo $complete ? '30' : '[53px]' ?>">
			<?php echo file_get_contents(WPBB_PLUGIN_DIR . 'public/assets/icons/trophy.svg'); ?>
			<h1 class="tw-mt-16 tw-mb-12 tw-font-700 tw-text-30 md:tw-text-48 lg:tw-text-64">
				<?php echo $complete && $bracket_winner ? "{$bracket_winner->name} Wins" : esc_html(get_the_title()); ?>
			</h1>
			<?php if ($complete) : ?>
				<h3 class="tw-text-20 tw-font-400 ">
					<?php echo esc_html(get_the_title()); ?>
				</h3>
			<?php endif; ?>
			<!-- <?php echo $complete ? wpbb_share_bracket_btn(get_permalink()) : wpbb_score_bracket_btn(get_permalink() . 'results'); ?> -->
			<!-- <?php echo $complete ? '' : wpbb_score_bracket_btn(get_permalink() . 'results'); ?> -->
		</div>
		<div class="tw-flex tw-flex-col tw-gap-16">
		<h2 class="!tw-text-white/50 tw-text-24 tw-font-500"><?php echo count($plays) > 0 ? "Bracket Plays" : "No Players in this Bracket"?></h2>
			<div class="tw-flex tw-flex-col tw-gap-16">
				<?php
				foreach ($plays as $i => $play) {
					echo wpbb_leaderboard_play_list_item($play, $i === 0 && $complete, $show_scores, $complete);
					// echo wpbb_leaderboard_play_list_item($play, true, $complete);
				}
				?>
			</div>
		</div>
	</div>
</div>
