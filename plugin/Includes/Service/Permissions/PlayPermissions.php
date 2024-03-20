<?php
namespace WStrategies\BMB\Includes\Service\Permissions;

use WP_Post;
use WStrategies\BMB\Includes\Domain\Play;
use WStrategies\BMB\Includes\Repository\PlayRepo;

class PlayPermissions implements PermissionsServiceInterface {
  private $play_repo;

  public function __construct($opts = []) {
    $this->play_repo = $opts['play_repo'] ?? new PlayRepo();
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
      case 'wpbb_create_payment_intent':
        return $this->is_author($user_id, $play);
      default:
        return false;
    }
  }

  public static function get_caps(): array {
    return ['wpbb_view_play', 'wpbb_print_play', 'wpbb_create_payment_intent'];
  }

  private function user_can_view_play($user_id, Play $play): bool {
    if ($this->is_author($user_id, $play)) {
      return true;
    }
    return $play->is_tournament_entry;
  }

  private function user_can_print_play($user_id, $play): bool {
    return $this->user_can_view_play($user_id, $play);
  }

  public static function is_author($user_id, $play): bool {
    return !empty($user_id) && (int) $play->author === (int) $user_id;
  }

  public static function is_bustable(WP_Post|int|null $play_post): bool {
    return self::is_play_public($play_post) && !self::bust_disabled($play_post);
  }

  public static function is_play_public(WP_Post|int|null $play_post): bool {
    $public_tags = ['bmb_vip_profile', 'bmb_vip_featured'];
    return has_tag($public_tags, $play_post->ID);
  }

  public static function bust_disabled(WP_Post|int|null $play_post): bool {
    return has_tag('bmb_no_bust', $play_post);
  }
}
