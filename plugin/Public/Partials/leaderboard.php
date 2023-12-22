<?php
namespace WStrategies\BMB\Public\Partials;

use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Repository\BracketPlayRepo;
use WStrategies\BMB\Includes\Repository\BracketRepo;

$page = get_query_var( 'paged' );
$play_repo = new BracketPlayRepo();
$bracket_repo = new BracketRepo();
$bracket = $bracket_repo->get( get_the_ID() );
$bracket_winner = $bracket->get_winning_team();
$post_status = get_post_status();
$complete = $post_status === 'complete';
$scored = $post_status === 'score';
$show_scores = $complete || $scored;

$query = [
		'post_status' => 'publish',
		'bracket_id' => $bracket->id,
		'busted_play_id' => [
			'compare' => 'NOT EXISTS',
		],
		'orderby' => 'accuracy_score',
		'order' => 'DESC',
	];

if ($bracket->results_first_updated_at) {
  $query['date_query'] = [
    [
      'column' => 'post_modified_gmt',
      'before' => $bracket->results_first_updated_at->format( 'Y-m-d H:i:s' ),
      'inclusive' => true,
    ]
  ];
}
$plays = $play_repo->get_all(
	$query,
	[
		'fetch_picks' => true,
	]
);

$has_winner = false;
$num_plays = count($plays);
if ($num_plays === 1) {
	$has_winner = true;
} else if ($num_plays > 1) {
	$first_play = $plays[0];
	$second_play = $plays[1];
	$has_winner = $first_play->accuracy_score !== $second_play->accuracy_score;
}

function score_bracket_btn($endpoint) {
	ob_start();
?>
	<a href="<?php echo $endpoint; ?>" class="tw-flex tw-justify-center tw-items-center !tw-text-off-black tw-gap-8 tw-py-12 tw-px-16 tw-rounded-8 tw-bg-yellow tw-font-500 tw-mt-16">
		<?php echo file_get_contents(WPBB_PLUGIN_DIR . 'Public/assets/icons/trophy_small.svg'); ?>
		<span>Score Bracket</span>
	</a>
<?php
	return ob_get_clean();
}

function share_bracket_btn($endpoint) {
	ob_start();
?>
	<a href="#" class="tw-flex tw-justify-center tw-items-center tw-text-black tw-gap-8 tw-py-12 tw-px-16 tw-rounded-8 tw-bg-white tw-font-500 tw-mt-16">
		<?php echo file_get_contents(WPBB_PLUGIN_DIR . 'Public/assets/icons/share.svg'); ?>
		<span>Share with contestants</span>
	</a>
<?php
	return ob_get_clean();
}

function leaderboard_play_list_item(BracketPlay $play, $winner = false, $show_score = false, $complete = false) {
	$play_id = $play->id;
	$play_author = $play->author;
	$author_name = get_the_author_meta('user_login', $play_author);
  $customer = new \WC_Customer($play_author);
  $state = $customer->get_billing_state();
	$time_ago = human_time_diff(get_the_time('U', $play_id), current_time('timestamp')) . ' ago';
	$winning_team = $play->get_winning_team();
	$winning_team_name = $winning_team ? $winning_team->name : '';
	$score = $play->accuracy_score;
	$winner = $winner && $show_score;
	$view_play_link = get_permalink($play_id);

	ob_start();
?>
	<div class="tw-flex tw-justify-between <?php echo $winner ? 'tw-flex-col sm:tw-flex-row tw-border-2 tw-border-solid tw-border-green tw-rounded-16 tw-p-20 sm:tw-p-30 tw-gap-16' : 'sm:tw-px-30' ?>">
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
			<div class="tw-flex tw-items-center <?php echo $winner ? 'tw-gap-20' : 'tw-gap-16' ?>">
				<div class="tw-flex tw-flex-col <?php echo $winner ? 'tw-gap-8' : 'tw-gap-4' ?>">
				<div class="wpbb-lb-winning-team-name-container tw-px-8 tw-text-center tw-py-4 tw-bg-white tw-text-dd-blue tw-font-700 <?php echo $winner ? 'tw-text-20' : 'tw-text-16' ?>" data-team-name="<?php echo esc_html($winning_team_name)?>" data-target-width="<?php echo 115 ?>">
				</div>
					<span class="<?php echo $winner ? 'tw-text-16' : 'tw-text-12' ?> tw-font-500<?php echo $winner ? ' tw-text-white/50' : '' ?>">
						winning team
					</span>
				</div>
				<div class="tw-flex tw-flex-col tw-flex-grow">
          <div class="<?php echo $winner ? 'tw-text-24 sm:tw-text-32' : 'tw-text-20 sm:tw-text-24'?>">
            <span class="tw-font-700">
              <?php echo esc_html($author_name); ?>
            </span>
            <span class="tw-font-400">
              <?php echo $state ? "- $state" : '' ?>
            </span>
          </div>
					<span class="tw-text-white/50 <?php echo $winner ? 'tw-text-16' : 'tw-text-12' ?> tw-font-500">
						<?php echo "played " . esc_html($time_ago); ?>
					</span>
				</div>
			</div>
		</div>
		<a href="<?php echo $view_play_link ?>" class="tw-flex tw-justify-center tw-items-center tw-gap-4 <?php echo $winner ? 'tw-self-start sm:tw-self-end' : 'tw-self-center' ?> tw-text-white tw-text-16 tw-font-500 <?php echo $complete ? 'hover:tw-text-green' : 'hover:tw-text-yellow' ?>">
			<?php echo file_get_contents(WPBB_PLUGIN_DIR . 'Public/assets/icons/arrow_up_right.svg'); ?>
			<span class="<?php echo $winner ? '' : 'tw-hidden sm:tw-inline'?>">View Play</span>
		</a>
	</div>
<?php
	return ob_get_clean();
}

?>
<div class="wpbb-reset tw-bg-dd-blue tw-flex tw-justify-center">
	<div class="tw-max-w-screen-lg tw-flex tw-flex-grow tw-flex-col tw-gap-30 tw-px-20 lg:tw-px-0 tw-py-60">

		<div class="wpbb-leaderboard-header<?php echo $complete ? ' wpbb-tourney-complete tw-border-2 tw-border-solid tw-border-green' : '' ?> tw-flex tw-flex-col tw-items-start tw-rounded-16 tw-pt-[66px] tw-px-30 <?php echo $complete ? 'tw-pb-30' : 'tw-pb-[53px]' ?>">
			<?php echo file_get_contents(WPBB_PLUGIN_DIR . 'Public/assets/icons/trophy.svg'); ?>
			<h1 class="tw-mt-16 tw-mb-12 tw-font-700 tw-text-30 md:tw-text-48 lg:tw-text-64">
				<?php echo $complete && $bracket_winner ? "{$bracket_winner->name} Wins" : esc_html(get_the_title()); ?>
			</h1>
			<?php if ($complete) : ?>
				<h3 class="tw-text-20 tw-font-400 ">
					<?php echo esc_html(get_the_title()); ?>
				</h3>
			<?php endif; ?>
			<!-- <?php echo $complete ? share_bracket_btn(get_permalink()) : score_bracket_btn(get_permalink() . 'results'); ?> -->
			<!-- <?php echo $complete ? '' : score_bracket_btn(get_permalink() . 'results'); ?> -->
		</div>
		<div class="tw-flex tw-flex-col tw-gap-20">
		<h2 class="!tw-text-white/50 tw-text-24 tw-font-500"><?php echo count($plays) > 0 ? "Bracket Plays" : "No Players in this Bracket"?></h2>
			<div class="tw-flex tw-flex-col tw-gap-20">
				<?php
				foreach ($plays as $i => $play) {
					echo leaderboard_play_list_item($play, $play->is_winner, $show_scores, $complete);
				}
				?>
			</div>
		</div>
	</div>
</div>
