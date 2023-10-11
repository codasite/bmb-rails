<?php
require_once 'wpbb-dashboard-common.php';
$shared_dir = plugin_dir_path(dirname(__FILE__)) . 'shared/';
require_once $shared_dir . 'wpbb-partials-common.php';
require_once $shared_dir . 'wpbb-tournaments-common.php';
require_once $shared_dir . 'wpbb-paginatino-widget.php';
require_once plugin_dir_path(dirname(__FILE__, 3)) . 'includes/repository/class-wpbb-bracket-play-repo.php';

$play_repo = new Wpbb_BracketPlayRepo();

$paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;

$the_query = new WP_Query([
	'post_type' => Wpbb_BracketPlay::get_post_type(),
	'author' => get_current_user_id(),
	'posts_per_page' => 6,
	'paged' => $paged,
	'post_status' => 'any'
]);

$num_pages = $the_query->max_num_pages;

$plays = $play_repo->get_all(
	$the_query,
	[
		'fetch_tournament' => true,
		'fetch_template' => true,
		'fetch_results' => true
	]
);

function play_list_item(Wpbb_BracketPlay $play) {
	$title = $play->tournament?->title ?? $play->template?->title;
	$user_rank = 99999;
	$complete = $play->tournament?->status === 'complete';
	$play_id = $play->id;
	$tournament_id = $play->tournament_id;
	$view_link = get_permalink($play_id) . 'view';
	$leaderboard_link = get_permalink($tournament_id) . 'leaderboard';
	$trend_up = true;
	$trend_icon = $trend_up ? 'arrow_up.svg' : 'arrow_down.svg';
	$leaderboard_variant = $complete ? 'final' : 'primary';
	$accuracy_score = round($play->accuracy_score * 100);
	$show_score = $play->tournament?->has_results();
	$buster_play = $play->busted_id !== null;
	ob_start();
?>

	<div class="tw-flex tw-justify-between tw-p-30 tw-rounded-16 tw-border-2 tw-border-solid tw-border-blue/20 tw-bg-blue/5">
		<div class="tw-flex tw-flex-col tw-gap-20">
			<div class="tw-flex tw-gap-10 tw-flex-wrap">
				<h2 class="tw-font-700 tw-text-30 tw-text-white"><?php echo esc_html($title) ?></h2>
        <div class="tw-flex tw-gap-10 tw-flex-wrap">
					<?php echo $buster_play ? tournament_tag('buster', 'red') : '' ?>
          <!-- <?php echo tournament_tag('printed', 'green') ?>
					<?php echo tournament_tag('not printed', 'yellow', false) ?> -->
        </div>
      </div>
      <div class="tw-flex tw-gap-16">
        <!-- View this play and add to apparel -->
				<?php echo view_play_btn($view_link); ?>
        <!-- View the leaderboard for this tournament -->
				<?php echo $tournament_id ? view_leaderboard_btn($leaderboard_link, $leaderboard_variant) : null; ?>
      </div>
    </div>
    <div class="tw-flex tw-flex-col tw-justify-between tw-items-end">
      <!-- <div class="tw-flex tw-gap-4 tw-items-center">
				<?php echo file_get_contents(plugins_url("../../assets/icons/$trend_icon", __FILE__)); ?>
				<span class="tw-font-500 tw-text-16 tw-text-white"><?php echo esc_html($user_rank) ?></span>
				<span class="tw-font-500 tw-text-16 tw-text-white/50">Rank</span>
			</div> -->
			<?php if ($show_score) : ?>
        <div class="tw-flex tw-flex-col tw-items-end">
					<h2 class="tw-font-700 tw-text-48 tw-text-white"><?php echo esc_html($accuracy_score) ?>%</h2>
					<span class="tw-font-500 tw-text-12 tw-text-white">My Score</span>
				</div>
			<?php endif; ?>
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
		}
		?>
		<?php wpbb_pagination($paged, $num_pages); ?>
	</div>
</div>
