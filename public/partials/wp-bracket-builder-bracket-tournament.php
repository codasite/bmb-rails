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
        echo '<div id="wpbb-bracket-builder"></div>';
        break;
    case 'score':
        echo 'Error: Score Tournament page not implemented yet.';
        break;
    default:
        echo 'Error: view must be set to either "leaderboard", "play", or score.';
        break;
}
// if ($view === 'leaderboard') {
//     // render the leaderboard template
//     include 'wp-bracket-builder-tourney-leaderboard.php';
// } else if ($view === 'play') {
//     // render the play template
//     // This is not working
//     echo '<div id="wpbb-bracket-builder"></div>';
// } else {
//     // An error occurred
//     echo 'view must be set to either "leaderboard" or "play"';
// }
?>