<?php
$current_tab = get_query_var('tab');

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
	$classes = array(
		'wpbb-dashboard-nav-link',
		'wpbb-flex',
		'wpbb-gap-10',
		'wpbb-align-center',
		'wpbb-border-radius-8',
		'wpbb-padding-16',
	);
	if ($tab === $current_tab) {
		$classes[] = 'active';
	}
	ob_start();
?>
	<a href="<?php echo get_permalink() . $tab; ?>" class="<?php echo implode(' ', $classes); ?>" data-tab="<?php echo $tab; ?>">
		<?php echo file_get_contents(plugins_url($icon, __FILE__)); ?>
		<span class="wpbb-dashboard-nav-link-label"><?php echo $label; ?></span>
	</a>
<?php
	return ob_get_clean();
}

?>
<div class="wpbb-dashboard">
	<nav class="wpbb-dashboard-nav">
		<h4 class="wpbb-dashboard-nav-title">Dashboard</h4>
		<ul class="wpbb-dashboard-nav-list">
			<li><?php echo wpbb_get_nav_link('profile', $current_tab, 'Profile', '../../assets/icons/user.svg'); ?></li>
			<li><?php echo wpbb_get_nav_link('templates', $current_tab, 'My Templates', '../../assets/icons/file.svg'); ?></li>
			<li><?php echo wpbb_get_nav_link('tournaments', $current_tab, 'My Tournaments', '../../assets/icons/signal.svg'); ?></li>
			<li><?php echo wpbb_get_nav_link('play-history', $current_tab, 'My Play History', '../../assets/icons/clock.svg'); ?></li>
		</ul>
	</nav>
	<div class="wpbb-dashboard-content">
		<?php include $template; ?>
	</div>
</div>