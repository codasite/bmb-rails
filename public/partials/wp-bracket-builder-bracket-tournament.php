<?php
/*
Template Name: Bracket Builder Tournament
*/
$view = get_query_var('view');
if ($view === 'leaderboard') {
    // render the leaderboard template
    include 'wp-bracket-builder-tourney-leaderboard.php';
} else if ($view === 'play') {
    // render the play template
    // This is not working
    echo '<div id="wpbb-bracket-builder"></div>';
} else {
    // An error occurred
    echo 'view must be set to either "leaderboard" or "play"';
}
?>