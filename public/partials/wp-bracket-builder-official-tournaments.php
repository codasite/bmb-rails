<?php
require_once('shared/wp-bracket-builder-partials-constants.php');
require_once('shared/wp-bracket-builder-partials-common.php');

$tournaments = array(
	array(
		"name" => "Lakeside High Football",
		"id" => 1,
		"num_teams" => 16,
		"num_plays" => 3,
		"completed" => false,
	),
	array(
		"name" => "College Basketball",
		"id" => 2,
		"num_teams" => 6,
		"num_plays" => 999,
		"completed" => true,
	),
	array(
		"name" => "Midwest Baseball",
		"id" => 3,
		"num_teams" => 8,
		"num_plays" => 103,
		"completed" => true,
	),
);

$page = get_query_var('paged');
function wpbb_get_official_tournaments() {
	$args = array(
		'post_type' => 'bracket_tournament',
		'posts_per_page' => -1,
	);
	$tournaments = get_posts($args);
	return $tournaments;
}

function wpbb_sort_button($label, $endpoint, $active = false) {
	$base_cls = [
		'tw-flex',
		'tw-items-center',
		'tw-justify-center',
		'tw-text-16',
		'tw-font-500',
		'tw-rounded-8',
		'tw-py-8',
		'tw-px-16',
	];

	$inactive_cls = [
		'tw-border',
		'tw-border-solid',
		'tw-border-white/50',
	];
	$active_cls = [
		'tw-bg-white',
		'tw-text-dark-blue',
	];

	$cls_list = array_merge($base_cls, $active ? $active_cls : $inactive_cls);
	ob_start();
?>
	<a class="<?php echo implode(' ', $cls_list) ?>" href="<?php echo esc_url($endpoint) ?>">
		<?php echo esc_html($label) ?>
	</a>
<?php
	return ob_get_clean();
}

function wpbb_tournament_sort_buttons() {
	$all_endpoint = get_permalink();
	$status = get_query_var('status');
	$live_endpoint = add_query_arg('status', LIVE_STATUS, $all_endpoint);
	$scored_endpoint = add_query_arg('status', SCORED_STATUS, $all_endpoint);
	ob_start();
?>
	<div class="tw-flex tw-justify-center tw-gap-10 tw-py-11">
		<?php echo wpbb_sort_button('All', $all_endpoint, !($status)); ?>
		<?php echo wpbb_sort_button('Live', $live_endpoint, $status === LIVE_STATUS); ?>
		<?php echo wpbb_sort_button('Scored', $scored_endpoint, $status === SCORED_STATUS); ?>
	</div>
<?php
	return ob_get_clean();
}

?>
<div class="wpbb-reset wpbb-official-tourneys tw-flex tw-flex-col tw-gap-30">
	<div class="tw-flex tw-flex-col tw-py-30 tw-gap-15 tw-items-center">
		<?php echo file_get_contents(plugins_url('../assets/icons/logo_dark.svg', __FILE__)); ?>
		<h1 class="tw-text-80 tw-font-700">Official Tournaments</h1>
	</div>
	<div class="tw-flex tw-flex-col tw-gap-15">
		<?php echo wpbb_tournament_sort_buttons(); ?>
		<?php foreach ($tournaments as $tournament) : ?>
			<?php echo public_tournament_list_item($tournament); ?>
		<?php endforeach; ?>
	</div>
</div>