<?php

namespace WStrategies\BMB\Public\Partials\shared;

class BracketOptionPermissions {
  public static function user_can_perform_action($option_name, $bracket) {
    switch ($option_name) {
      case BracketOptions::DELETE_BRACKET:
        return current_user_can('wpbb_delete_bracket', $bracket->id);

      case BracketOptions::EDIT_BRACKET:
        return current_user_can('wpbb_edit_bracket', $bracket->id);

      case BracketOptions::SET_FEE:
        return current_user_can('wpbb_add_bracket_fee', $bracket->id);

      case BracketOptions::LOCK_TOURNAMENT:
        return current_user_can('wpbb_edit_bracket', $bracket->id);

      // These options don't have explicit permission checks in the original code
      case BracketOptions::MOST_POPULAR_PICKS:
      case BracketOptions::SHARE_BRACKET:
      case BracketOptions::DUPLICATE_BRACKET:
        return true;

      default:
        return false;
    }
  }
}
