<?php
namespace WStrategies\BMB\Includes\Service\Play;

use WStrategies\BMB\Includes\Controllers\ApiListeners\BracketPlayCreateListenerBase;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Service\BracketProduct\BracketProductUtils;

class FreePaidPlayService extends BracketPlayCreateListenerBase {
  private BracketProductUtils $bracket_product_utils;

  public function __construct($args = []) {
    $this->bracket_product_utils =
      $args['bracket_product_utils'] ?? new BracketProductUtils();
  }

  public function filter_before_play_added(Play $play): Play {
    if ($this->should_mark_play_paid($play)) {
      $play->is_paid = true;
    }
    return $play;
  }

  public function should_mark_play_paid(Play $play): bool {
    if (
      !$this->bracket_product_utils->has_bracket_fee($play->bracket_id) ||
      $play->is_paid
    ) {
      return false;
    }
    if (current_user_can('wpbb_play_bracket_for_free', $play->bracket_id)) {
      return true;
    }
    return false;
  }
}
