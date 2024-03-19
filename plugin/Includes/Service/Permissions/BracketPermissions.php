<?php
namespace WStrategies\BMB\Includes\Service\Permissions;

use WStrategies\BMB\Includes\Domain\Bracket;
use WStrategies\BMB\Includes\Repository\BracketRepo;
use WStrategies\BMB\Includes\Repository\PlayRepo;
use WStrategies\BMB\Includes\Service\BracketLeaderboardService;

class BracketPermissions implements PermissionsServiceInterface {
  private $bracket_repo;
  private $play_repo;
  private $leaderboard_service;

  public function __construct($opts = []) {
    $this->bracket_repo = $opts['bracket_repo'] ?? new BracketRepo();
    $this->play_repo =
      $opts['play_repo'] ??
      new PlayRepo([
        'bracket_repo' => $this->bracket_repo,
      ]);
    $this->leaderboard_service =
      $opts['leaderboard_service'] ?? new BracketLeaderboardService();
  }

  public function has_cap($cap, $user_id, $post_id): bool {
    $bracket = $this->bracket_repo->get($post_id);

    if (!$bracket) {
      return false;
    }

    if ($cap == 'wpbb_add_bracket_fee') {
      return current_user_can('wpbb_create_paid_bracket') &&
        $this->is_bracket_author($user_id, $bracket);
    }

    if ($this->is_bracket_author($user_id, $bracket)) {
      return true;
    }

    switch ($cap) {
      case 'wpbb_play_bracket':
        return $this->user_can_play_bracket($user_id, $bracket);
      case 'wpbb_view_bracket_chat':
        return $this->user_can_view_bracket_chat($user_id, $bracket);
      case 'wpbb_play_bracket_for_free':
        return $this->user_can_play_bracket_for_free($user_id, $bracket);
      default:
        return false;
    }
  }

  public static function get_caps(): array {
    return [
      'wpbb_delete_bracket',
      'wpbb_edit_bracket',
      'wpbb_play_bracket',
      'wpbb_play_bracket_for_free',
      'wpbb_add_bracket_fee',
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
    if (!$user_id) {
      return false;
    }
    $num_plays = $this->leaderboard_service->get_num_plays([
      'author' => $user_id,
      'bracket_id' => $bracket->id,
    ]);
    if ($num_plays > 0) {
      return true;
    }
    return false;
  }

  private function is_bracket_author($user_id, $bracket): bool {
    return (int) $bracket->author === (int) $user_id;
  }

  private function user_can_play_bracket_for_free($user_id, $bracket): bool {
    if (!$bracket->has_fee()) {
      return true;
    }
    return $this->user_has_paid_play_for_bracket($user_id, $bracket);
  }

  private function user_has_paid_play_for_bracket(
    int $user_id,
    Bracket $bracket
  ): bool {
    return $this->play_repo->get_count([
      'bracket_id' => $bracket->id,
      'author' => $user_id,
      'is_paid' => true,
    ]) > 0;
  }
}
