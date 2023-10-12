<?php
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wpbb-bracket-play-repo.php';
require_once WPBB_PLUGIN_DIR . 'includes/service/class-wpbb-bracket-product-utils.php';

$post = get_post();
if (!$post || $post->post_type !== 'bracket_play') {
    return
        '<div class="alert alert-danger" role="alert">
					Play not found.
				</div>';
}
$play_repo = new Wpbb_BracketPlayRepo();
$product_utils = new Wpbb_BracketProductUtils();
$play = $play_repo->get($post);
$bracket_product_archive_url = $product_utils->get_bracket_product_archive_url();

wp_localize_script(
    'wpbb-bracket-builder-react',
    'wpbb_ajax_obj',
    array(
        'play' => $play,
        'nonce' => wp_create_nonce('wp_rest'),
        'rest_url' => get_rest_url() . 'wp-bracket-builder/v1/',
        'redirect_url' => $bracket_product_archive_url
    )
);

?> <div id="wpbb-view-play"></div>
