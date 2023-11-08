<?php
require_once WPBB_PLUGIN_DIR . 'includes/repository/class-wpbb-bracket-play-repo.php';
require_once WPBB_PLUGIN_DIR . 'includes/service/bracket-product/class-wpbb-bracket-product-utils.php';

$post = get_post();
if (!$post || $post->post_type !== 'bracket_play') {
    return
        '<div class="alert alert-danger" role="alert">
					Play not found.
				</div>';
}

?> <div id="wpbb-view-play"></div>
