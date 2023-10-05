<?php

require_once(plugin_dir_path(dirname(__FILE__, 3)) . 'includes/service/class-wp-bracket-builder-notification-service.php');

if (empty($current_tab)) {
	$current_tab = 'profile';
}

switch ($current_tab) {
	case 'profile':
		$template = 'wp-bracket-builder-my-profile.php';
		break;
	case 'templates':
		$template = 'wp-bracket-builder-my-templates.php';
		break;
	case 'tournaments':
		$template = 'wp-bracket-builder-my-tournaments.php';
		break;
	case 'play-history':
		$template = 'wp-bracket-builder-my-play-history.php';
		break;
	default:
		$template = 'wp-bracket-builder-my-profile.php';
		break;
}


function wpbb_get_nav_link($tab, $current_tab, $label, $icon) {
	$active = $tab === $current_tab;
	ob_start();
?>
	<a class="tw-flex tw-gap-10 tw-items-center tw-rounded-8 tw-p-16 tw-whitespace-nowrap<?php echo $active ? ' tw-bg-blue' : ' tw-bg-white/10'; ?>" href="<?php echo get_permalink() . $tab; ?>" data-tab="<?php echo $tab; ?>">
		<?php echo file_get_contents(plugins_url($icon, __FILE__)); ?>
		<span><?php echo $label; ?></span>
	</a>
<?php
	return ob_get_clean();
}

?>
<div class="wpbb-dashboard tw-text-white tw-font-sans tw-flex tw-flex-col md:tw-flex-row tw-gap-30 lg:tw-gap-60 leading-none tw-uppercase">
	<nav>
		<h4 class="tw-text-white/50 tw-text-16 tw-text-500 tw-mb-15">Dashboard</h4>
		<ul class="tw-flex tw-flex-col tw-gap-15 tw-p-0 tw-m-0">
			<li class="tw-font-500 tw-text-20 tw-list-none"><?php echo wpbb_get_nav_link('profile', $current_tab, 'Profile', '../../assets/icons/user.svg'); ?></li>
			<li class="tw-font-500 tw-text-20 tw-list-none"><?php echo wpbb_get_nav_link('templates', $current_tab, 'My Templates', '../../assets/icons/file.svg'); ?></li>
			<li class="tw-font-500 tw-text-20 tw-list-none"><?php echo wpbb_get_nav_link('tournaments', $current_tab, 'My Tournaments', '../../assets/icons/signal.svg'); ?></li>
			<li class="tw-font-500 tw-text-20 tw-list-none"><?php echo wpbb_get_nav_link('play-history', $current_tab, 'My Play History', '../../assets/icons/clock.svg'); ?></li>
		</ul>
	</nav>
	<div class="tw-flex-grow">
		<?php include $template; ?>
	</div>
</div>