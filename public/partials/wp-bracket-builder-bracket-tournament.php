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
    case 'score':
        echo 'Error: Score Tournament page not implemented yet.';
        break;
    default:
        echo '<div id="wpbb-play-tournament-builder"></div>';
        break;
}
