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

?> <div id="wpbb-bust-play"></div>
