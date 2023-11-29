<?php
require_once WPBB_PLUGIN_DIR .
  'includes/service/permissions/class-wpbb-permissions-service-interface.php';

class Wpbb_PlayPermissions implements Wpbb_PermissionsServiceInterface {
  private $play_repo;

  public function __construct($opts = []) {
    $this->play_repo = $opts['play_repo'] ?? new Wpbb_BracketPlayRepo();
  }

  public function has_cap($cap, $user_id, $post_id): bool {
    $play = $this->play_repo->get($post_id);

    if (!$play) {
      return false;
    }

    switch ($cap) {
      case 'wpbb_view_play':
        return $this->user_can_view_play($user_id, $play);
      case 'wpbb_print_play':
        return $this->user_can_print_play($user_id, $play);
      default:
        return false;
    }
  }

  public static function get_caps(): array {
    return ['wpbb_view_play', 'wpbb_print_play'];
  }

  private function user_can_view_play($user_id, $play): bool {
    if ($this->is_author($user_id, $play)) {
      return true;
    }
    return $play->is_printed;
  }

  private function user_can_print_play($user_id, $play): bool {
    return $this->user_can_view_play($user_id, $play);
  }

  private function is_author($user_id, $play): bool {
    return (int) $play->author === (int) $user_id;
  }
}
