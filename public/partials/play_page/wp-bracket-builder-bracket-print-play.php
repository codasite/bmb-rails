<?php
require_once plugin_dir_path(dirname(__FILE__, 3)) . 'includes/repository/class-wp-bracket-builder-bracket-play-repo.php';

$post = get_post();
if (!$post || $post->post_type !== 'bracket_play') {
    return
        '<div class="alert alert-danger" role="alert">
					Play not found.
				</div>';
}
$play_repo = new Wp_Bracket_Builder_Bracket_Play_Repository();
$play = $play_repo->get($post);

$theme = get_query_var('theme') ? get_query_var('theme') : 'light';
$position = get_query_var('position') ? get_query_var('position') : 'top';
$inch_height = get_query_var('inch_height') ? get_query_var('inch_height') : 16;
$inch_width = get_query_var('inch_width') ? get_query_var('inch_width') : 12;

wp_localize_script(
    'wpbb-bracket-builder-react',
    'wpbb_ajax_obj',
    array(
        'play' => $play,
        'nonce' => wp_create_nonce('wp_rest'),
        'print_options' => [
            'theme' => $theme,
            'position' => $position,
            'inch_height' => $inch_height,
            'inch_width' => $inch_width,
        ]
    )
);

?> <div id="wpbb-print-play"></div>