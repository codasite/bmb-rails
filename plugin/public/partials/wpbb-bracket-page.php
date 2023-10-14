<?php
$view = get_query_var('view');
switch ($view) {
    case 'play':
        echo '<div id="wpbb-play-bracket-builder"></div>';
        break;
    case 'leaderboard':
        include 'wpbb-bracket-leaderboard.php';
        break;
    case 'copy':
        echo '<div id="wpbb-bracket-builder"></div>';
        break;
    case 'results':
        echo '<div id="wpbb-bracket-results-builder"></div>';
        break;
    default:
        echo '<div id="wpbb-play-bracket-builder"></div>';
        break;
}
