<?php
namespace WStrategies\BMB\Public\Partials;

$error_page = '<div class="alert alert-danger" role="alert">
		Bracket not found.
	</div>';
$post = get_post();
if (!$post || $post->post_type !== 'bracket') {
	return $error_page;
}

$view = get_query_var('view');
switch ($view) {
    case 'play':
        echo '<div id="wpbb-play-bracket"></div>';
        break;
    case 'leaderboard':
        $leaderboard = new LeaderboardPage();
        echo $leaderboard->render();
        break;
    case 'copy':
        echo '<div id="wpbb-bracket-builder"></div>';
        break;
    case 'results':
		if (!current_user_can('wpbb_edit_bracket', $post->ID)) {
            header('HTTP/1.0 403 Forbidden');
			include(WPBB_PLUGIN_DIR . 'Public/Error/403.php');
			return;
		}
        echo '<div id="wpbb-bracket-results-builder"></div>';
        break;
    case 'chat':
        include(WPBB_PLUGIN_DIR . 'Public/Partials/BracketPage/bracket-chat.php');
        break;
    default:
        echo '<div id="wpbb-play-bracket"></div>';
        break;
}
