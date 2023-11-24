<?php
require_once('shared/wpbb-brackets-common.php');
require_once('shared/wpbb-partials-common.php');
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wpbb-user-profile-repo.php';

function wpbb_vip_switcher($bracket_or_play) {
	if ($bracket_or_play instanceof Wpbb_Bracket) {
		return wpbb_vip_bracket_card($bracket_or_play, ['show_profile_link' => true]);
	} else if ($bracket_or_play instanceof Wpbb_BracketPlay) {
		return wpbb_vip_play_card($bracket_or_play, ['show_profile_link' => true]);
	}
}

function wpbb_vip_play_card($play, $options = []) {
	$show_profile_link = $options['show_profile_link'] ?? false;
	// Should probably handle this differently
	$profile_link = '';
	if ($show_profile_link) {
		$profile_repo = new Wpbb_UserProfileRepo();
		$profile = $profile_repo->get_by_user($play->author);
		if ($profile->url) {
			$profile_link = $profile->url;
		}
	}
	$title = $play->title;
	$id = $play->id;
	$thumbnail = get_the_post_thumbnail_url($id);
	$play_link = get_permalink($id) . 'bust';
	$leaderboard_link = get_permalink($play->bracket_id) . 'leaderboard';
	$buttons = [
		view_play_btn($play_link),
		view_leaderboard_btn($leaderboard_link),
	];
	return wpbb_vip_card($title, $thumbnail, ['buttons' => $buttons, 'profile_link' => $profile_link]);
}

function wpbb_vip_bracket_card($bracket, $options = []) {
	$show_profile_link = $options['show_profile_link'] ?? false;
	// Should probably handle this differently
	$profile_link = '';
	if ($show_profile_link) {
		$profile_repo = new Wpbb_UserProfileRepo();
		$profile = $profile_repo->get_by_user($bracket->author);
		if ($profile->url) {
			$profile_link = $profile->url;
		}
	}
	$title = $bracket->title;
	$id = $bracket->id;
	$thumbnail = get_the_post_thumbnail_url($id);
	$play_link = get_permalink($id) . 'play';
	$leaderboard_link = get_permalink($id) . 'leaderboard';
	$buttons = [
		play_bracket_btn($play_link),
		view_leaderboard_btn($leaderboard_link),
	];
	return wpbb_vip_card($title, $thumbnail, ['buttons' => $buttons, 'profile_link' => $profile_link]);
}

function wpbb_view_profile_btn($link) {
	ob_start();
	?>
	<a href="<?php echo esc_url($link) ?>" class="tw-flex tw-items-center tw-justify-center tw-gap-8 tw-text-white tw-px-16 tw-py-4 tw-rounded-8 tw-bg-dd-blue/60 hover:tw-bg-dd-blue">
		<span class="tw-text-14 tw-font-600">View Profile</span>
		<?php echo wpbb_icon('arrow_up_right') ?>
	</a>
	<?php
	return ob_get_clean();
}

function wpbb_vip_card($title, $thumbnail, array $options = []) {
	$buttons = $options['buttons'] ?? [];
	$profile_link = $options['profile_link'] ?? '';
	ob_start();
	?>
	<div class="tw-flex tw-flex-col">
		<div class="tw-relative tw-bg-[url(<?php echo $thumbnail ?>)] tw-bg-center tw-bg-cover tw-bg-no-repeat tw-bg-white tw-rounded-t-16 tw-h-[324px]">
			<?php if ($profile_link) : ?>
			<div class="tw-absolute tw-top-20 tw-right-20">
				<?php echo wpbb_view_profile_btn($profile_link)?>
			</div>
			<?php endif; ?>
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
