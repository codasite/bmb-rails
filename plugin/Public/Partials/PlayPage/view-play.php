<?php
namespace WStrategies\BMB\Public\Partials\PlayPage;

$post = get_post();
if (!$post || $post->post_type !== 'bracket_play') {
  return '<div class="alert alert-danger" role="alert">
					Play not found.
				</div>';
}
?> <div id="wpbb-view-play"></div>
