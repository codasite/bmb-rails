<?php
$view = get_query_var('view');
switch ($view) {
    case 'view':
        include 'wp-bracket-builder-bracket-view-play.php';
        break;
    case 'print':
        include 'wp-bracket-builder-bracket-print-play.php';
        break;
    default:
        include 'wp-bracket-builder-bracket-view-play.php';
        break;
}
