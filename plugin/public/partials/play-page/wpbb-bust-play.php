<?php
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wpbb-bracket-play-repo.php';
require_once WPBB_PLUGIN_DIR . 'includes/service/permissions/class-wpbb-play-permissions.php';

$post = get_post();
$repo = new Wpbb_BracketPlayRepo();
$play = $repo->get($post->ID);
if (!$play) {
	header('HTTP/1.0 404 Not Found');
	include WPBB_PLUGIN_DIR . 'public/error/404.php';
	return;
}

?> <div id="wpbb-bust-play"></div>
