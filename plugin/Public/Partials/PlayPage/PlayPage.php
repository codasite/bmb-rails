<?php

namespace WStrategies\BMB\Public\Partials\PlayPage;

use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Public\Error\ErrorPage;
use WStrategies\BMB\Public\Partials\TemplateInterface;

class PlayPage implements TemplateInterface {
  public function __construct(private PlayRepo $play_repo = new PlayRepo()) {
  }
  public function render(): false|string {
    $play = $this->play_repo->get(get_the_ID());
    $error_page = $this->get_error_page($play);
    if ($error_page) {
      return $error_page->render();
    }

    $view = get_query_var('view');

    switch ($view) {
      case 'view':
        $el_id = 'wpbb-view-play';
        break;
      case 'bust':
        $el_id = 'wpbb-bust-play';
        break;
      case 'replay':
        $el_id = 'wpbb-play-bracket';
        break;
      default:
        $el_id = 'wpbb-view-play';
        break;
    }
    return "<div id='$el_id'></div>";
  }

  public function get_error_page(?Play $play): ?ErrorPage {
    $status_code = 200;
    if (!$play) {
      $status_code = 404;
    } elseif (!current_user_can('wpbb_view_play', $play->id)) {
      $status_code = 403;
    }
    if ($status_code !== 200) {
      return new ErrorPage($status_code);
    }
    return null;
  }
}
