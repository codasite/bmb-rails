<?php
$post_type = get_query_var('post_type');
switch ($post_type) {
    case 'bracket_play':
        include 'wp-bracket-builder-bracket-print-play.php';
        break;
    default:
        include 'wp-bracket-builder-bracket-print-play.php';
        break;
}
