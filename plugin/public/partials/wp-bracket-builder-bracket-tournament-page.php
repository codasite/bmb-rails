<?php
/*
Template Name: Bracket Builder Tournament
*/
$view = get_query_var('view');
switch ($view) {
    case 'leaderboard':
        include 'wp-bracket-builder-tourney-leaderboard.php';
        break;
    case 'play':
        echo '<div id="wpbb-play-tournament-builder"></div>';
        break;
    case 'results':
        echo '<div id="wpbb-tournament-results-builder"></div>';
        break;
    default:
        echo '<div id="wpbb-play-tournament-builder"></div>';
        break;
}
