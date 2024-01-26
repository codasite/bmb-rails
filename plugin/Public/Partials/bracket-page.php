<?php
namespace WStrategies\BMB\Public\Partials;

$post = get_post();
if (!$post || $post->post_type !== 'bracket') {
    header('HTTP/1.0 404 Not Found');
    include(WPBB_PLUGIN_DIR . 'Public/Error/404.php');
    return;
}

$view = get_query_var('view', 'play');
$action = get_query_var('action');

switch ($view) {
    case 'leaderboard':
        $leaderboard = new LeaderboardPage();
        echo $leaderboard->render();
        break;
    case 'copy':
        echo '<div id="wpbb-bracket-builder"></div>';
        break;
    case 'results':
        switch ($action) {
            case 'update':
                if (!current_user_can('wpbb_edit_bracket', $post->ID)) {
                    header('HTTP/1.0 403 Forbidden');
                    include(WPBB_PLUGIN_DIR . 'Public/Error/403.php');
                    return;
                }
                echo '<div id="wpbb-update-bracket-results"></div>';
                break;
            default:
                echo '<div id="wpbb-view-bracket-results"></div>';
                break;
        }
        break;
    case 'chat':
        include(WPBB_PLUGIN_DIR . 'Public/Partials/BracketPage/bracket-chat.php');
        break;
    default:
        echo '<div id="wpbb-play-bracket"></div>';
        break;
}
