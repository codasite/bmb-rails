<?php

namespace WStrategies\BMB\Public\Partials\shared;

class BracketIconButtonPermissions {
  public static function user_can_perform_action($option_name, $bracket) {
    switch ($option_name) {
      case 'delete_bracket':
        return current_user_can('wpbb_delete_bracket', $bracket->id);

      case 'edit_bracket':
        return current_user_can('wpbb_edit_bracket', $bracket->id);

      case 'set_fee':
        return current_user_can('wpbb_add_bracket_fee', $bracket->id);

      case 'duplicate_bracket':
        return current_user_can('wpbb_edit_bracket', $bracket->id);

      case 'lock_tournament':
        return current_user_can('wpbb_edit_bracket', $bracket->id);

      // These options don't have explicit permission checks in the original code
      case 'most_popular_picks':
      case 'share_bracket':
        return true;

      default:
        return false;
    }
  }
}
