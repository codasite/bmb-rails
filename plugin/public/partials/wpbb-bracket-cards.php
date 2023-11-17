<?php
require_once('shared/wpbb-brackets-common.php');
require_once('shared/wpbb-partials-common.php');

function wpbb_vip_switcher($bracket_or_play) {
	if ($bracket_or_play instanceof Wpbb_Bracket) {
		return wpbb_vip_bracket_card($bracket_or_play);
	} else if ($bracket_or_play instanceof Wpbb_BracketPlay) {
		return wpbb_vip_play_card($bracket_or_play);
	}
}

function wpbb_vip_play_card($play) {
	$title = $play->title;
	$id = $play->id;
	$thumbnail = get_the_post_thumbnail_url($id);
	$play_link = get_permalink($id) . 'bust';
	$leaderboard_link = get_permalink($play->bracket_id) . 'leaderboard';
	$buttons = [
		view_play_btn($play_link),
		view_leaderboard_btn($leaderboard_link),
	];
	return wpbb_vip_card($title, $thumbnail, $buttons);
}

function wpbb_vip_bracket_card($bracket) {
	$title = $bracket->title;
	$id = $bracket->id;
	$thumbnail = get_the_post_thumbnail_url($id);
	$play_link = get_permalink($id) . 'play';
	$leaderboard_link = get_permalink($id) . 'leaderboard';
	$buttons = [
		play_bracket_btn($play_link),
		view_leaderboard_btn($leaderboard_link),
	];
	return wpbb_vip_card($title, $thumbnail, $buttons);
}

function wpbb_vip_card($title, $thumbnail, array $buttons = []) {
	ob_start();
	?>
	<div class="tw-flex tw-flex-col">
		<div class="tw-bg-[url(<?php echo $thumbnail ?>)] tw-bg-center tw-bg-cover tw-bg-no-repeat tw-bg-white tw-rounded-t-16 tw-h-[324px]">
			<div class="tw-flex tw-flex-col tw-justify-end tw-flex-grow tw-px-30 tw-rounded-t-16 tw-bg-gradient-to-t tw-from-[#03073C] tw-to-[72%] tw-border-solid tw-border-white/20 tw-border-2 tw-border-y-none tw-h-full">
				<h3 class="tw-text-30 tw-text-black"><?php echo esc_html($title) ?></h3>
			</div>
		</div>
		<div class="tw-flex tw-flex-col sm:tw-flex-row md:tw-flex-col lg:tw-flex-row tw-pt-20 tw-gap-10 tw-px-30 tw-pb-30 tw-bg-dd-blue tw-bg-gradient-to-r tw-from-[#03073C]/50 tw-to-50% tw-rounded-b-16 tw-border-solid tw-border-white/20 tw-border-2 tw-border-t-none">
			<?php 
				foreach ($buttons as $button) {
					echo $button;
				}
			?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
?>
