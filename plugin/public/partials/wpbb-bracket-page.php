<?php
require_once WPBB_PLUGIN_DIR . 'includes/service/product-preview/class-wpbb-product-preview-service.php';
$error_page = '<div class="alert alert-danger" role="alert">
		Bracket not found.
	</div>';
$post = get_post();
if (!$post || $post->post_type !== 'bracket') {
	return $error_page;
}
$bracket_repo = new Wpbb_BracketRepo();
$bracket = $bracket_repo->get(post: $post);
if (!$bracket) {
	return $error_page;
}

wp_localize_script(
	'wpbb-bracket-builder-react',
	'wpbb_page_obj',
	array(
		'bracket' => $bracket,
	)
);

$view = get_query_var('view');
switch ($view) {
    case 'play':
        echo '<div id="wpbb-play-bracket"></div>';
        break;
    case 'leaderboard':
        include 'wpbb-bracket-leaderboard.php';
        break;
    case 'copy':
        echo '<div id="wpbb-bracket-builder"></div>';
        break;
    case 'results':
		if (!current_user_can('wpbb_edit_bracket', $bracket->id)) {
			include(WPBB_PLUGIN_DIR . 'public/error/401.php');
			return;
		}
        echo '<div id="wpbb-bracket-results-builder"></div>';
        break;
    default:
        echo '<div id="wpbb-play-bracket"></div>';
        break;
}
