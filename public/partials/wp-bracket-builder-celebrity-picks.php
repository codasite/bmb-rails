<?php
require_once('shared/wp-bracket-builder-tournaments-common.php');

$plays = array(
	array(
		"play_title" => "Terrell Owen's March Maddress Picks",
		"play_id" => 1,
		"tournament_id" => 1,
	),
	array(
		"play_title" => "Aaron Rodgers' NCAA NIT Bracket",
		"play_id" => 2,
		"tournament_id" => 2,
	),
	array(
		"play_title" => "NCAA Womens World Series 2024",
		"play_id" => 3,
		"tournament_id" => 3,
	),
);

$tournaments = array(
	array(
		"name" => "NCAA College Football 2024 Hosted by Ahmad Merritt",
		"id" => 1,
		"num_teams" => 16,
		"num_plays" => 3,
		"completed" => false,
	),
	array(
		"name" => "NCAA Womens World Series 2024 Hosted by Johnny Manziel",
		"id" => 2,
		"num_teams" => 16,
		"num_plays" => 999,
		"completed" => true,
	)
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

function wpbb_tournament_sort_buttons() {
	$all_endpoint = get_permalink();
	$status = get_query_var('status');
	$live_endpoint = add_query_arg('status', LIVE_STATUS, $all_endpoint);
	$scored_endpoint = add_query_arg('status', SCORED_STATUS, $all_endpoint);
	ob_start();
?>
	<div class="tw-flex tw-gap-10 tw-py-11">
		<?php echo wpbb_sort_button('All', $all_endpoint, !($status)); ?>
		<?php echo wpbb_sort_button('Live', $live_endpoint, $status === LIVE_STATUS); ?>
		<?php echo wpbb_sort_button('Scored', $scored_endpoint, $status === SCORED_STATUS); ?>
	</div>
<?php
	return ob_get_clean();
}

?>
<div class="wpbb-reset wpbb-official-tourneys tw-flex tw-flex-col">
	<div class="tw-flex tw-flex-col md:tw-flex-row-reverse tw-py-60 tw-gap-15 tw-items-center md:tw-justify-between">
		<?php echo file_get_contents(plugins_url('../assets/icons/logo_dark.svg', __FILE__)); ?>
		<h1 class="tw-text-64 sm:tw-text-80 tw-font-700 tw-text-center md:tw-text-left">Celebrity Picks</h1>
	</div>
	<div class="tw-flex tw-flex-col tw-gap-30 tw-py-60">
		<h2 class="tw-text-48 tw-font-700">Plays</h2>
	</div>
	<div class="tw-flex tw-flex-col tw-gap-30 tw-py-60">
		<h2 class="tw-text-48 tw-font-700">Tournaments</h2>
		<div class="tw-flex tw-flex-col tw-gap-15">
			<?php echo wpbb_tournament_sort_buttons(); ?>
			<?php foreach ($tournaments as $tournament) : ?>
				<?php echo public_tournament_list_item($tournament); ?>
			<?php endforeach; ?>
		</div>
	</div>
</div>