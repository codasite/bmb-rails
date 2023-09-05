<?php
require_once('shared/wp-bracket-builder-tournaments-common.php');
require_once('shared/wp-bracket-builder-partials-common.php');

$plays = array(
	array(
		"play_title" => "Terrell Owen's March Maddress Picks",
		"play_id" => 1,
		"tournament_id" => 1,
		"thumbnail" => "http://localhost:8888/wordpress-new/wp-content/uploads/2023/09/Terrell.png",
	),
	array(
		"play_title" => "Aaron Rodgers' NCAA NIT Bracket",
		"play_id" => 2,
		"tournament_id" => 2,
		"thumbnail" => "http://localhost:8888/wordpress-new/wp-content/uploads/2023/09/Aaron.png",
	),
	array(
		"play_title" => "Aaron Rodgers' NCAA NIT Bracket",
		"play_id" => 2,
		"tournament_id" => 2,
		"thumbnail" => "http://localhost:8888/wordpress-new/wp-content/uploads/2023/09/Aaron.png",
	),
	array(
		"play_title" => "Aaron Rodgers' NCAA NIT Bracket",
		"play_id" => 2,
		"tournament_id" => 2,
		"thumbnail" => "http://localhost:8888/wordpress-new/wp-content/uploads/2023/09/Aaron.png",
	),
	// array(
	// 	"play_title" => "NCAA Womens World Series 2024",
	// 	"play_id" => 3,
	// 	"tournament_id" => 3,
	// ),
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

function wpbb_bust_bracket_btn($endpoint) {
	ob_start();
?>
	<a class="tw-flex tw-items-center tw-text-white tw-px-16 tw-py-11 tw-rounded-8 tw-border tw-border-solid tw-justify-center tw-gap-10 tw-bg-red/20 tw-border-red" href="<?php echo esc_url($endpoint) ?>">
		<?php echo file_get_contents(plugins_url('../assets/icons/lightning.svg', __FILE__)); ?>
		<span class="tw-font-700">Bust Bracket</span>
	</a>
<?php
	return ob_get_clean();
}

function wpbb_celebrity_play_list_item($play) {
	$title = $play['play_title'];
	$id = $play['play_id'];
	$tournament_id = $play['tournament_id'];
	// $thumbnail = get_the_post_thumbnail_url($tournament_id);
	$thumbnail = $play['thumbnail'];
	$play_link = get_permalink() . 'tournaments/' . $tournament_id . '/plays/' . $id;
	$bust_link = get_permalink() . 'tournaments/' . $tournament_id . '/bust/' . $id;
	ob_start();
?>
	<div class="tw-flex tw-flex-col">
		<div class="tw-bg-[url(<?php echo $thumbnail ?>)] tw-bg-center tw-bg-no-repeat tw-bg-white tw-rounded-t-16 tw-h-[324px]">
			<div class="tw-flex tw-flex-col tw-justify-end tw-flex-grow tw-px-30 tw-rounded-t-16 tw-bg-gradient-to-t tw-from-[#03073C] tw-to-[72%] tw-border-solid tw-border-white/20 tw-border-2 tw-border-y-none tw-h-full">
				<h3 class="tw-text-30 tw-text-black"><?php echo esc_html($title) ?></h3>
			</div>
		</div>
		<div class="tw-flex tw-flex-col sm:tw-flex-row md:tw-flex-col lg:tw-flex-row tw-pt-20 tw-gap-10 tw-px-30 tw-pb-30 tw-bg-dd-blue tw-bg-gradient-to-r tw-from-[#03073C]/50 tw-to-50% tw-rounded-b-16 tw-border-solid tw-border-white/20 tw-border-2 tw-border-t-none">
			<?php echo view_play_btn($play_link); ?>
			<?php echo wpbb_bust_bracket_btn($bust_link); ?>
		</div>
	</div>
<?php
	return ob_get_clean();
}

?>
<div class="wpbb-reset tw-bg-dd-blue">
	<div class="tw-flex tw-flex-col">
		<div class="tw-flex tw-flex-col md:tw-flex-row-reverse tw-py-60 tw-gap-15 tw-items-center md:tw-justify-between tw-max-w-screen-lg tw-m-auto tw-px-20 lg:tw-px-0">
			<?php echo file_get_contents(plugins_url('../assets/icons/logo_dark.svg', __FILE__)); ?>
			<h1 class="tw-text-64 sm:tw-text-80 tw-font-700 tw-text-center md:tw-text-left">Celebrity Picks</h1>
		</div>
		<div class="wpbb-celeb-plays tw-py-60 tw-px-20 lg:tw-px-0">
			<div class="tw-flex tw-flex-col tw-gap-30 tw-max-w-screen-lg tw-m-auto ">
				<h2 class="tw-text-48 tw-font-700 ">Plays</h2>
				<div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-10">
					<?php foreach ($plays as $play) : ?>
						<?php echo wpbb_celebrity_play_list_item($play); ?>
					<?php endforeach; ?>
				</div>
			</div>

		</div>
		<div class="tw-flex tw-flex-col tw-gap-30 tw-py-60 tw-max-w-screen-lg tw-m-auto tw-px-20 lg:tw-px-0">
			<h2 class="tw-text-48 tw-font-700">Tournaments</h2>
			<div class="tw-flex tw-flex-col tw-gap-15">
				<?php echo wpbb_tournament_sort_buttons(); ?>
				<?php foreach ($tournaments as $tournament) : ?>
					<?php echo public_tournament_list_item($tournament); ?>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>