<?php
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
