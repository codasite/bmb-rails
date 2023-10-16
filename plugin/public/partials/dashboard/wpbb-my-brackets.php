<?php
require_once 'wpbb-dashboard-common.php';
$shared_dir = plugin_dir_path(dirname(__FILE__)) . 'shared/';
require_once $shared_dir . 'wpbb-partials-common.php';
require_once $shared_dir . 'wpbb-brackets-common.php';
require_once $shared_dir . 'wpbb-pagination-widget.php';
require_once plugin_dir_path(dirname(__FILE__, 3)) . 'includes/repository/class-wpbb-bracket-repo.php';
require_once plugin_dir_path(dirname(__FILE__, 3)) . 'includes/repository/class-wpbb-bracket-play-repo.php';

$bracket_repo = new Wpbb_BracketRepo();
$play_repo = new Wpbb_BracketPlayRepo();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archive_bracket_id'])) {
	if (wp_verify_nonce($_POST['archive_bracket_nonce'], 'archive_bracket_action')) {
		$bracket_repo->update($_POST['archive_bracket_id'], [
			'status' => 'private',
		]);
	}
}

$paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;

$paged_status = get_query_var('status');
if (empty($paged_status)) {
	$paged_status = 'all';
}

$all_status = ['publish', 'private', 'scored', 'complete'];
$active_status = ['publish', 'private'];
$scored_status = ['scored', 'complete'];

if ($paged_status === 'all') {
	$post_status = $all_status;
} else if ($paged_status === 'active') {
	$post_status = $active_status;
} else if ($paged_status === 'scored') {
	$post_status = $scored_status;
} else {
	$post_status = $all_status;
}

$the_query = new WP_Query([
	'post_type' => Wpbb_Bracket::get_post_type(),
	'author' => get_current_user_id(),
	'posts_per_page' => 6,
	'paged' => $paged,
	'post_status' => $post_status,
]);

$num_pages = $the_query->max_num_pages;

$brackets = $bracket_repo->get_all($the_query, ['fetch_template' => true]);

function score_bracket_btn($endpoint, $bracket) {

	ob_start();
?>
	<a class="tw-border tw-border-solid tw-border-yellow tw-bg-yellow/15 tw-px-16 tw-py-12 tw-flex tw-justify-center sm:tw-justify-start tw-gap-10 tw-items-center tw-rounded-8 tw-text-white" href="<?php echo esc_url($endpoint) ?>">
		<?php echo file_get_contents(WPBB_PLUGIN_DIR . 'public/assets/icons/trophy_24.svg'); ?>
		<span class="tw-font-500">Update Results</span>
	</a>
<?php
	return ob_get_clean();
}

function go_live_btn(string $id) {
	ob_start();
	?>
  <button data-bracket-id="<?php echo $id ?>"
          class="wpbb-publish-bracket-button tw-border tw-border-solid tw-border-blue tw-bg-blue/15 tw-min-w-[190px] tw-px-16 tw-py-12 tw-flex tw-gap-10 tw-items-center tw-justify-center tw-rounded-8 hover:tw-bg-blue tw-font-sans tw-text-white tw-uppercase tw-cursor-pointer">
		<?php echo file_get_contents(WPBB_PLUGIN_DIR . 'public/assets/icons/signal.svg'); ?>
    <span class="tw-font-700">Go Live</span>
  </button>
	<?php
	return ob_get_clean();
}

function private_bracket_buttons($bracket) {
	$bracket_play_link = get_permalink($bracket->id) . 'play';
	ob_start();
?>
	<div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-8 sm:tw-gap-16">
		<!-- This goes to the Play Bracket page -->
		<?php echo play_bracket_btn($bracket_play_link, $bracket); ?>
		<!-- This goes to the Score Bracket page -->
		<?php echo go_live_btn($bracket->id); ?>
	</div>
<?php

	return ob_get_clean();
}

function live_bracket_buttons($bracket) {
	$bracket_play_link = get_permalink($bracket->id) . 'play';
	$bracket_score_link = get_permalink($bracket->id) . 'results';
	$leaderboard_link = get_permalink($bracket->id) . 'leaderboard';
	ob_start();
?>
	<div class="tw-flex tw-flex-col sm:tw-flex-row sm:tw-items-end sm:tw-justify-between tw-flex-wrap tw-gap-8 sm:tw-gap-16">
		<div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-8 sm:tw-gap-16">
			<!-- This goes to the Play Bracket page -->
			<?php echo play_bracket_btn($bracket_play_link, $bracket); ?>
			<!-- This goes to the Score Bracket page -->
			<?php echo score_bracket_btn($bracket_score_link, $bracket); ?>
		</div>
		<!-- This goes to the Leaderboard page -->
		<?php echo view_leaderboard_btn($leaderboard_link, 'compact'); ?>
	</div>
<?php

	return ob_get_clean();
}

function completed_bracket_buttons($bracket) {
	$play_link = get_permalink($bracket->id) . 'play';
	$leaderboard_link = get_permalink($bracket->id) . 'leaderboard';
	ob_start();
?>
	<div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between sm:tw-items-end tw-gap-8">
		<!-- This goes to the Play Bracket page -->
		<?php echo add_to_apparel_btn($play_link); ?>
		<!-- This goes to the Leaderboard page -->
		<?php echo view_leaderboard_btn($leaderboard_link, 'compact'); ?>
	</div>
<?php
	return ob_get_clean();
}

function get_bracket_buttons($bracket) {
	switch ($bracket->status) {
		case 'publish':
			return live_bracket_buttons($bracket);
		case 'private':
			return private_bracket_buttons($bracket);
		case 'scored':
			return live_bracket_buttons($bracket);
		case 'complete':
			return completed_bracket_buttons($bracket);
		case 'archive':
			return private_bracket_buttons($bracket);
		default:
			return '';
	}
}

function edit_bracket_btn($bracket) {
	$id = $bracket->id;
	$title = $bracket->title;
	$date = $bracket->date;
	return icon_btn('pencil.svg', 'submit', classes: "wpbb-edit-bracket-button", attributes: "data-bracket-id='$id' data-bracket-title='$title' data-bracket-date='$date'");
}


function share_bracket_btn($bracket) {
	$play_link = get_permalink($bracket->id) . 'play';
	return icon_btn('link.svg', 'submit', classes: "wpbb-share-bracket-button", attributes: "data-play-bracket-url=$play_link");
}

function duplicate_bracket_btn($bracket) {
	$copy_link = get_permalink($bracket->id) . 'copy';
	return icon_link('copy.svg', $copy_link);
}

function archive_bracket_btn($bracket) {
	$endpoint = get_permalink() . 'brackets/';
	$bracket_id = $bracket->id;
	ob_start();
?>
	<form method="post" action="<?php echo esc_url($endpoint) ?>">
		<input type="hidden" name="archive_bracket_id" value="<?php echo esc_attr($bracket_id) ?>" />
		<?php wp_nonce_field('archive_bracket_action', 'archive_bracket_nonce'); ?>
		<?php echo icon_btn('archive.svg', 'submit'); ?>
	</form>
<?php
	return ob_get_clean();
}

function delete_bracket_btn($bracket) {
	$bracket_id = $bracket->id;
	return icon_btn('trash.svg', 'submit', classes: "wpbb-delete-bracket-button", attributes: "data-bracket-id='$bracket_id' data-bracket-title='$bracket->title'");
}

function private_bracket_icon_buttons($bracket) {
	ob_start();
	?>
	<div class="tw-flex tw-gap-10 tw-items-center">
		<?php echo edit_bracket_btn($bracket); ?>
		<?php echo duplicate_bracket_btn($bracket); ?>
		<?php echo archive_bracket_btn($bracket); ?>
		<?php echo delete_bracket_btn($bracket); ?>
	</div>
	<?php
	return ob_get_clean();
}

function live_bracket_icon_buttons($bracket) {
	ob_start();
	?>
	<div class="tw-flex tw-gap-10 tw-items-center">
		<?php echo edit_bracket_btn($bracket); ?>
		<?php echo share_bracket_btn($bracket); ?>
		<?php echo duplicate_bracket_btn($bracket); ?>
		<?php echo archive_bracket_btn($bracket); ?>
		<?php echo delete_bracket_btn($bracket); ?>
	</div>
	<?php
	return ob_get_clean();
}

function get_bracket_icon_buttons($bracket) {
	switch ($bracket->status) {
		case 'publish':
			return live_bracket_icon_buttons($bracket);
		case 'private':
			return private_bracket_icon_buttons($bracket);
		case 'scored':
			return live_bracket_icon_buttons($bracket);
		case 'complete':
			return live_bracket_icon_buttons($bracket);
		case 'archive':
			return private_bracket_icon_buttons($bracket);
		default:
			return '';
	}
}

function archived_bracket_tag() {
	return bracket_tag('Archive', 'white/50', false);
}

function private_bracket_tag() {
	return bracket_tag('Private', 'blue', false);
}

function get_bracket_tag($status) {
	switch ($status) {
		case 'publish':
			return live_bracket_tag();
		case 'private':
			return private_bracket_tag();
		case 'scored':
			return scored_bracket_tag();
		case 'complete':
			return completed_bracket_tag();
		case 'archive':
			return archived_bracket_tag();
		default:
			return '';
	}
}

function bracket_list_item($bracket, Wpbb_BracketPlayRepo $play_repo) {
	// TODO: fix play_repo->get_all_by_bracket
	// $play_repo->get_all_by_bracket($bracket->id);

	$title = $bracket->title;
	$num_teams = $bracket->num_teams;
	$num_plays = $play_repo ? $play_repo->get_count([
		'meta_query' => [
			[
				'key' => 'bracket_id',
				'value' => $bracket->id,
			],
		],
	]) : 0;

	$id = $bracket->id;
	ob_start();
?>
	<div class="tw-border-2 tw-border-solid tw-border-white/15 tw-flex tw-flex-col tw-gap-10 tw-p-30 tw-rounded-16">
    <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between sm:tw-items-center tw-gap-8">
      <span class="tw-font-500 tw-text-12"><?php echo esc_html($num_teams) ?>-Team Bracket</span>
      <div class="tw-flex tw-gap-4 tw-items-center">
				<?php echo get_bracket_tag($bracket->status); ?>
				<?php echo file_get_contents(WPBB_PLUGIN_DIR . 'public/assets/icons/bar_chart.svg'); ?>
        <span class="tw-font-500 tw-text-20 tw-text-white"><?php echo esc_html($num_plays) ?></span>
        <span class="tw-font-500 tw-text-20 tw-text-white/50">Plays</span>
      </div>
    </div>
    <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between tw-gap-15 md:tw-justify-start sm:tw-items-center">
      <h2 class="tw-text-white tw-font-700 tw-text-30"><?php echo esc_html($title) ?></h2>
			<?php echo get_bracket_icon_buttons($bracket); ?>
    </div>
    <div class="tw-mt-10">
			<?php echo get_bracket_buttons($bracket); ?>
		</div>
	</div>
<?php
	return ob_get_clean();
}

?>

<div id="wpbb-my-brackets-modals"></div>
<div class="tw-flex tw-flex-col tw-gap-15">
	<h1 class="tw-mb-8 tw-text-64 tw-font-700 tw-leading-none">My Brackets</h1>
	<a href="<?php echo get_permalink(get_page_by_path('bracket-builder'))?>" class="tw-flex tw-gap-16 tw-items-center tw-justify-center tw-border-solid tw-border tw-border-white tw-rounded-8 tw-p-16 tw-bg-white/15 tw-text-white tw-font-sans tw-uppercase tw-cursor-pointer hover:tw-text-black hover:tw-bg-white">
		<?php echo file_get_contents(WPBB_PLUGIN_DIR . 'public/assets/icons/signal.svg'); ?>
		<span class="tw-font-700 tw-text-24 tw-leading-none">Create Bracket</span>
	</a>
	<div class="tw-flex tw-gap-10 tw-gap-10 tw-py-11">
		<?php echo wpbb_sort_button('All', get_permalink() . "brackets/?status=all", $paged_status === 'all'); ?>
		<?php echo wpbb_sort_button('Active', get_permalink() . "brackets/?status=active", $paged_status === 'active'); ?>
		<?php echo wpbb_sort_button('Scored', get_permalink() . "brackets/?status=scored", $paged_status === 'scored'); ?>
	</div>
	<div class="tw-flex tw-flex-col tw-gap-15">
		<?php foreach ($brackets as $bracket) {
			echo bracket_list_item($bracket, $play_repo);
		} ?>
		<?php wpbb_pagination($paged, $num_pages); ?>
	</div>
</div>
