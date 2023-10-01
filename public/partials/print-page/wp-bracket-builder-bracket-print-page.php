<?php
$post_type = get_query_var('posttype');
switch ($post_type) {
    case 'bracket_play':
        include 'wp-bracket-builder-bracket-print-play.php';
        break;
    default:
        return '<div class="alert alert-danger" role="alert">Invalid post type</div>';
        break;
}
