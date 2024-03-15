<?php
namespace WStrategies\BMB\Includes\Domain;

use WP_Query;
use WP_User;
use WStrategies\BMB\Includes\Service\BracketLeaderboardService;

class UserProfile extends PostBase {
  public ?WP_User $wp_user;
  private BracketLeaderboardService $leaderboard_service;

  public function __construct(array $data = []) {
    parent::__construct($data);
    $this->wp_user = $data['wp_user'] ?? null;
    $this->leaderboard_service =
      $data['leaderboard_service'] ?? new BracketLeaderboardService();
  }

  public static function get_post_type(): string {
    return 'user_profile';
  }

  public function get_post_meta(): array {
    return [];
  }

  public function get_update_post_data(): array {
    return [];
  }

  public function get_update_post_meta(): array {
    return [];
  }

  // public static function get_current() {
  //   $user = wp_get_current_user();
  //   return new self($user);
  // }

  public function __get($key) {
    return $this->wp_user->$key;
  }

  public function get_num_plays(): int {
    return $this->leaderboard_service->get_num_plays([
      'author' => $this->wp_user->ID,
    ]);
  }

  public function get_bmb_tournament_wins(): int {
    $query = new WP_Query([
      'post_type' => Play::get_post_type(),
      'author' => $this->wp_user->ID,
      'posts_per_page' => -1,
      'is_winner' => true,
      'bmb_official' => true,
      'busted_play_id' => [
        'compare' => 'NOT EXISTS',
      ],
    ]);
    return $query->found_posts;
  }

  public function get_tournament_wins(): int {
    $query = new WP_Query([
      'post_type' => Play::get_post_type(),
      'author' => $this->wp_user->ID,
      'posts_per_page' => -1,
      'is_winner' => true,
      'busted_play_id' => [
        'compare' => 'NOT EXISTS',
      ],
    ]);
    return $query->found_posts;
  }

  public function get_total_accuracy(): float {
    return 0.5;
  }

  public function get_bio(): string {
    return $this->content;
  }
}
