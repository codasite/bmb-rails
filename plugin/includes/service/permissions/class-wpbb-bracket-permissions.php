<?php
require_once WPBB_PLUGIN_DIR .
  'includes/service/permissions/class-wpbb-permissions-service-interface.php';

class Wpbb_BracketPermissions implements Wpbb_PermissionsServiceInterface {
  private $bracket_repo;
  private $play_repo;

  public function __construct($opts = []) {
    $this->bracket_repo = $opts['bracket_repo'] ?? new Wpbb_BracketRepo();
    $this->play_repo = $opts['play_repo'] ?? new Wpbb_BracketPlayRepo();
  }

  public function has_cap($cap, $user_id, $post_id): bool {
    $bracket = $this->bracket_repo->get($post_id);

    if (!$bracket) {
      return false;
    }

    if ((int) $bracket->author === (int) $user_id) {
      return true;
    }

    switch ($cap) {
      case 'wpbb_play_bracket':
        return $this->user_can_play_bracket($user_id, $bracket);
      case 'wpbb_view_bracket_chat':
        return $this->user_can_view_bracket_chat($user_id, $bracket);
      default:
        return false;
    }
  }

  public static function get_caps(): array {
    return [
      'wpbb_delete_bracket',
      'wpbb_edit_bracket',
      'wpbb_play_bracket',
      'wpbb_view_bracket_chat',
    ];
  }

  private function user_can_play_bracket($user_id, $bracket): bool {
    $playable_status = ['publish', 'score', 'complete'];
    if (in_array($bracket->status, $playable_status)) {
      return true;
    }
    return false;
  }

  private function user_can_view_bracket_chat($user_id, $bracket): bool {
    $num_plays = $this->play_repo->get_count([
      'author' => $user_id,
      'bracket_id' => $bracket->id,
      'is_printed' => true,
    ]);
    if ($num_plays > 0) {
      return true;
    }
    return false;
  }
}
