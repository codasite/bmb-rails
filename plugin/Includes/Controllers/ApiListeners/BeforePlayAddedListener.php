<?php
namespace WStrategies\BMB\Includes\Controllers\ApiListeners;

use WStrategies\BMB\Includes\Domain\Play;

class BeforePlayAddedListener extends BracketPlayCreateListenerBase {
  public function filter_before_play_added(Play $play): Play {
    $play->author = get_current_user_id();
    $play->bmb_official = has_tag('bmb_official', $play->bracket_id);
    return $play;
  }
}
