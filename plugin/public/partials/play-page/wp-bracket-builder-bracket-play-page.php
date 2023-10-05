<?php
$view = get_query_var('view');
switch ($view) {
    case 'view':
        include 'wp-bracket-builder-bracket-view-play.php';
        break;
    case 'bust':
        include 'wp-bracket-builder-bracket-bust-play.php';
        break;
    default:
        include 'wp-bracket-builder-bracket-view-play.php';
        break;
}
