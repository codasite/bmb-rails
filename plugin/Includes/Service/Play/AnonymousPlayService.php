<?php
namespace WStrategies\BMB\Includes\Service\Play;

use WStrategies\BMB\Includes\Controllers\ApiListeners\BracketPlayCreateListenerBase;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Utils;

class AnonymousPlayService extends BracketPlayCreateListenerBase {
  private Utils $utils;

  /**
   * @param array<mixed> $args
   */
  public function __construct($args = []) {
    $this->utils = $args['utils'] ?? new Utils();
  }

  public function filter_after_play_added(Play $play): Play {
    if (!is_user_logged_in()) {
      $bytes = random_bytes(32);
      $nonce = base64_encode($bytes);
      $this->utils->set_cookie('wpbb_anonymous_play_key', $nonce);

      update_post_meta($play->id, 'wpbb_anonymous_play_key', $nonce);
    }
    return $play;
  }
}
