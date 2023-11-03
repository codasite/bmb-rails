<?php
require_once 'wpbb-dashboard-common.php';
$shared_dir = plugin_dir_path(dirname(__FILE__)) . 'shared/';
require_once $shared_dir . 'wpbb-partials-common.php';
require_once $shared_dir . 'wpbb-brackets-common.php';
require_once $shared_dir . 'wpbb-pagination-widget.php';
require_once plugin_dir_path(dirname(__FILE__, 3)) . 'includes/repository/class-wpbb-bracket-play-repo.php';

$play_repo = new Wpbb_BracketPlayRepo();

$paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;

$the_query = new WP_Query([
	'post_type' => Wpbb_BracketPlay::get_post_type(),
	'author' => get_current_user_id(),
	'posts_per_page' => 6,
	'paged' => $paged,
	'post_status' => 'publish'
]);

$num_pages = $the_query->max_num_pages;

$plays = $play_repo->get_all(
	$the_query,
	[
		'fetch_bracket' => true,
		'fetch_results' => true
	]
);

function view_bust_btn($endpoint) {
	return view_play_btn($endpoint, 'View Bust');
}

function bust_again_btn($endpoint) {
	ob_start();
?>
	<a class="tw-border-red tw-border-solid tw-border tw-bg-red/15 hover:tw-bg-red hover:tw-text-dd-blue tw-px-16 tw-py-12 tw-flex tw-rounded-8 tw-text-white" href="<?php echo esc_url($endpoint) ?>">
		<span class="tw-font-500">Bust Again</span>
	</a>
<?php
	return ob_get_clean();
}

function get_default_play_buttons($play) {
	$complete = $play->bracket?->status === 'complete';
	$play_id = $play->id;
	$bracket_id = $play->bracket_id;
	$view_link = get_permalink($play_id) . 'view';
	$leaderboard_link = get_permalink($bracket_id) . 'leaderboard';
	$leaderboard_variant = $complete ? 'final' : 'primary';
	$show_leaderboard = $play->bracket?->status !== 'private';
	ob_start();
	?>
		<?php echo view_play_btn($view_link); ?>
		<?php echo $bracket_id && $show_leaderboard ? view_leaderboard_btn($leaderboard_link, $leaderboard_variant) : null; ?>
	<?php
	return ob_get_clean();
}

function get_buster_play_buttons($play) {
	$view_link = get_permalink($play->id) . 'view';
	$bust_again_link = get_permalink($play->busted_id) . 'bust';
	ob_start();
	?>
		<?php echo view_bust_btn($view_link); ?>
		<?php echo bust_again_btn($bust_again_link); ?>
	<?php
	return ob_get_clean();
}

function play_list_item(Wpbb_BracketPlay $play) {
	$title = $play->bracket?->title ?? $play->bracket?->title;
	$user_rank = 99999;
	$trend_up = true;
	$trend_icon = $trend_up ? 'arrow_up.svg' : 'arrow_down.svg';
	$accuracy_score = round($play->accuracy_score * 100);
	$show_score = $play->accuracy_score !== null;
	$buster_play = $play->busted_id !== null;
	$printed = $play->is_printed;
	ob_start();
?>

	<div class="tw-flex tw-justify-between tw-p-30 tw-rounded-16 tw-border-2 tw-border-solid tw-border-blue/20 tw-bg-blue/5">
		<div class="tw-flex tw-w-full tw-flex-col tw-gap-20">
			<div class="tw-flex tw-gap-10 tw-flex-wrap">
				<h2 class="tw-font-700 tw-text-20 sm:tw-text-30 tw-text-white"><?php echo esc_html($title) ?></h2>
        <div class="tw-flex tw-gap-10 tw-flex-wrap">
					<?php echo $buster_play ? bracket_tag('buster', 'red') : '' ?>
          <?php echo $printed ? bracket_tag('printed', 'green') : '' ?>
        </div>
      </div>
      <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-16">
				<?php echo $buster_play ? get_buster_play_buttons($play) : get_default_play_buttons($play) ?>
      </div>
    </div>
    <div class="tw-flex tw-flex-col tw-justify-between tw-items-end">
      <!-- <div class="tw-flex tw-gap-4 tw-items-center">
				<?php echo file_get_contents(WPBB_PLUGIN_DIR . "public/assets/icons/$trend_icon"); ?>
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
