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


function wpbb_get_nav_link($tab, $current_tab, $label) {
	$classes = array('wpbb-dashboard-nav-link');
	if ($tab === $current_tab) {
		$classes[] = 'active';
	}
	return '<a href="' . get_permalink() . $tab . '" class="' . implode(' ', $classes) . '" data-tab="' . $tab . '">' . $label . '</a>';
}

?>
<div class="wpbb-dashboard">
	<nav class="wpbb-dashboard-nav">
		<h4 class="wpbb-dashboard-nav-title">Dashboard</h4>
		<ul class="wpbb-dashboard-nav-list">
			<li><?php echo wpbb_get_nav_link('profile', $current_tab, 'Profile'); ?></li>
			<li><?php echo wpbb_get_nav_link('templates', $current_tab, 'My Templates'); ?></li>
			<li><?php echo wpbb_get_nav_link('tournaments', $current_tab, 'My Tournaments'); ?></li>
			<li><?php echo wpbb_get_nav_link('play-history', $current_tab, 'My Play History'); ?></li>
		</ul>
	</nav>
	<div class="wpbb-dashboard-content">
		<?php include $template; ?>
	</div>
</div>