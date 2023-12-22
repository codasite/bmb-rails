<?php
namespace WStrategies\BMB\Includes\Domain;

use WP_Query;
use WP_User;

class UserProfile extends PostBase {
  /**
   * The user object.
   *
   * @var WP_User
   */
  public $wp_user;

  public function __construct(array $data = []) {
    parent::__construct($data);
    $this->wp_user = isset($data['wp_user']) ? $data['wp_user'] : null;
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

  public function get_num_plays() {
    $query = new WP_Query([
      'post_type' => BracketPlay::get_post_type(),
      'author' => $this->wp_user->ID,
      'posts_per_page' => -1,
    ]);
    return $query->found_posts;
  }

  public function get_bmb_tournament_wins() {
    $query = new WP_Query([
      'post_type' => BracketPlay::get_post_type(),
      'author' => $this->wp_user->ID,
      'posts_per_page' => -1,
      'is_winner' => true,
      'bmb_official' => true,
    ]);
    return $query->found_posts;
  }

  public function get_total_accuracy() {
    return 0.5;
  }
}
