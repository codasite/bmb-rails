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
$preview_service = new Wpbb_ProductPreviewService();
$apparel_url = $preview_service->get_archive_url();
$play_history_url = get_permalink(get_page_by_path('dashboard')) . '?tab=play-history';


// $bracket_product_archive_url = $this->get_archive_url();

wp_localize_script(
	'wpbb-bracket-builder-react',
	'wpbb_ajax_obj',
	array(
		'bracket' => $bracket,
		// 'sentry_env' => $sentry_env,
		// 'sentry_dsn' => $sentry_dsn,
		'my_brackets_url' => get_permalink(get_page_by_path('dashboard')) . '?tab=brackets',
		'nonce' => wp_create_nonce('wp_rest'),
		'rest_url' => get_rest_url() . 'wp-bracket-builder/v1/',
		'redirect_url' => $apparel_url, // used to redirect to bracket-ready category page

		// 'bracket_product_archive_url' => $bracket_product_archive_url, // used to redirect to bracket-ready category page
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
        echo '<div id="wpbb-bracket-results-builder"></div>';
        break;
    default:
        echo '<div id="wpbb-play-bracket"></div>';
        break;
}
