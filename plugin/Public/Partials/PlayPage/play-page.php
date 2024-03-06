<?php
namespace WStrategies\BMB\Public\Partials\PlayPage;

use WStrategies\BMB\Includes\Repository\PlayRepo;

$post = get_post();
$repo = new PlayRepo();
$play = $repo->get($post->ID);
if (!$play) {
  header('HTTP/1.0 404 Not Found');
  include WPBB_PLUGIN_DIR . 'Public/Error/404.php';
  return;
}
if (!current_user_can('wpbb_view_play', $play->id)) {
  header('HTTP/1.0 403 Forbidden');
  include WPBB_PLUGIN_DIR . 'Public/Error/403.php';
  return;
}

$view = get_query_var('view');

switch ($view) {
  case 'view':
    include 'view-play.php';
    break;
  case 'bust':
    include 'bust-play.php';
    break;
  default:
    include 'view-play.php';
    break;
}
