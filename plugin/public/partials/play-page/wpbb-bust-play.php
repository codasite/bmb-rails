<?php
require_once plugin_dir_path(dirname(__FILE__, 3)) . 'includes/repository/class-wpbb-bracket-play-repo.php';

$post = get_post();
// if (!$post || $post->post_type !== 'bracket_play' || !has_tag('bustable', $post) || !has_tag('bmb_vip_play', $post)) {
if (!$post || $post->post_type !== 'bracket_play') {
    return
        '<div class="alert alert-danger" role="alert">
					Play not found.
				</div>';
}
$play_repo = new Wpbb_BracketPlayRepo();
$play = $play_repo->get($post);
$play_history_url = get_permalink(get_page_by_path('dashboard')) . '?tab=play-history';
// $bracket_product_archive_url = $this->get_archive_url();

wp_localize_script(
    'wpbb-bracket-builder-react',
    'wpbb_ajax_obj',
    array(
        'play' => $play,
        'play_history_url' => $play_history_url,
        'nonce' => wp_create_nonce('wp_rest'),
        'rest_url' => get_rest_url() . 'wp-bracket-builder/v1/',
        'redirect_url' => $play_history_url,
        'thumbnailUrl' => get_the_post_thumbnail_url($post->ID, 'thumbnail'),
    )
);

?> <div id="wpbb-bust-play"></div>
