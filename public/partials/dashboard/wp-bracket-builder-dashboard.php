<?php
$current_tab = get_query_var('tab');
if (empty($current_tab)) {
	$current_tab = 'profile';
}
?>
<div class="wpbb-dashboard">
	<nav class="wpbb-dashboard-nav">
		<ul>
			<li><a href="<?php echo get_permalink() . 'profile'; ?>" class="wpbb-dashboard-nav-link" data-tab="profile">Profile</a></li>
			<li><a href="<?php echo get_permalink() . 'templates'; ?>" class="wpbb-dashboard-nav-link" data-tab="my-templates">My Templates</a></li>
			<li><a href="<?php echo get_permalink() . 'tournaments'; ?>" class="wpbb-dashboard-nav-link" data-tab="my-tournaments">My Tournaments</a></li>
			<li><a href="<?php echo get_permalink() . 'play-history'; ?>" class="wpbb-dashboard-nav-link" data-tab="my-play-history">My Play History</a></li>
		</ul>
	</nav>
	<div class="wpbb-dashboard-content">
		<h1>Dashboard content</h1>
		<p>Current tab: <?php echo $current_tab; ?></p>
		<?php echo get_permalink(); ?>
	</div>
</div>