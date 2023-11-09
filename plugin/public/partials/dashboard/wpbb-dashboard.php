<?php

$current_tab = get_query_var('tab');

if (empty($current_tab)) {
	$current_tab = 'brackets';
}

switch ($current_tab) {
	case 'profile':
		$template = 'wpbb-my-profile.php';
		break;
	case 'brackets':
		$template = 'wpbb-my-brackets.php';
		break;
	case 'play-history':
		$template = 'wpbb-my-play-history.php';
		break;
	default:
		$template = 'wpbb-my-profile.php';
		break;
}


function wpbb_get_nav_link($tab, $current_tab, $label, $icon) {
	$active = $tab === $current_tab;
	ob_start();
?>
	<a class="tw-flex tw-gap-10 tw-items-center tw-rounded-8 tw-p-16 tw-whitespace-nowrap hover:tw-bg-blue<?php echo $active ? ' tw-bg-blue' : ' tw-bg-white/10'; ?>" href="<?php echo get_permalink() . $tab; ?>" data-tab="<?php echo $tab; ?>">
		<?php echo file_get_contents(WPBB_PLUGIN_DIR . '/public/assets/icons/' . $icon); ?>
		<span><?php echo $label; ?></span>
	</a>
<?php
	return ob_get_clean();
}

?>
<div class="tw-bg-dd-blue tw-py-60">
<div class="wpbb-dashboard tw-text-white tw-font-sans tw-flex tw-flex-col md:tw-flex-row tw-gap-30 lg:tw-gap-60 leading-none tw-uppercase tw-max-w-screen-xl tw-mx-auto tw-px-20">
	<nav>
		<h4 class="tw-text-white/50 tw-text-16 tw-font-500 tw-mb-15">Dashboard</h4>
		<ul class="tw-flex tw-flex-col tw-gap-15 tw-p-0 tw-m-0">
			<li class="tw-font-500 tw-text-20 tw-list-none"><?php echo wpbb_get_nav_link('profile', $current_tab, 'Profile', '../../assets/icons/user.svg'); ?></li>
			<li class="tw-font-500 tw-text-20 tw-list-none"><?php echo wpbb_get_nav_link('brackets', $current_tab, 'My Brackets', '../../assets/icons/signal.svg'); ?></li>
			<li class="tw-font-500 tw-text-20 tw-list-none"><?php echo wpbb_get_nav_link('play-history', $current_tab, 'My Play History', '../../assets/icons/clock.svg'); ?></li>
		</ul>
	</nav>
	<div class="tw-flex-grow">
		<?php include $template; ?>
	</div>
</div>
</div>
