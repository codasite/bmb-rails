<?php
namespace WStrategies\BMB\Includes\Service\Play;

use WStrategies\BMB\Includes\Controllers\ApiListeners\BracketPlayCreateListenerBase;
use WStrategies\BMB\Includes\Domain\BracketPlay;
use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductUtils;

class FreePaidPlayService extends BracketPlayCreateListenerBase {
  private BracketProductUtils $bracket_product_utils;

  public function __construct($args = []) {
    $this->bracket_product_utils =
      $args['bracket_product_utils'] ?? new BracketProductUtils();
  }

  public function filter_before_play_added(BracketPlay $play): BracketPlay {
    if (
      $this->bracket_product_utils->has_bracket_fee($play->bracket_id) &&
      current_user_can('wpbb_play_paid_bracket_for_free')
    ) {
      $play->is_paid = true;
    }
    return $play;
  }
}
