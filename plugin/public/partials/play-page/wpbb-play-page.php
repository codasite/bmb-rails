<?php
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wpbb-bracket-play-repo.php';

$post = get_post();
$repo = new Wpbb_BracketPlayRepo();
$play = $repo->get($post->ID);
if (!$play) {
	header('HTTP/1.0 404 Not Found');
	include WPBB_PLUGIN_DIR . 'public/error/404.php';
	return;
}
if (!current_user_can('wpbb_view_play', $play->id)) {
    header('HTTP/1.0 403 Forbidden');
    include WPBB_PLUGIN_DIR . 'public/error/403.php';
    return;
}

$view = get_query_var('view');

switch ($view) {
    case 'view':
        include 'wpbb-view-play.php';
        break;
    case 'bust':
        include 'wpbb-bust-play.php';
        break;
    default:
        include 'wpbb-view-play.php';
        break;
}
