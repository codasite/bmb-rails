<?php
namespace WStrategies\BMB\Includes\Repository;

use Exception;
use WP_Post;
use WP_Query;
use WP_User;
use wpdb;
use WStrategies\BMB\Includes\Domain\UserProfile;
use WStrategies\BMB\Includes\Utils;

class UserProfileRepo extends CustomPostRepoBase {
  /**
   * @var Utils
   */
  private $utils;

  /**
   * @var wpdb
   */
  private $wpdb;

  public function __construct() {
    global $wpdb;
    $this->wpdb = $wpdb;
    $this->utils = new Utils();
    parent::__construct();
  }

  /**
   * Get the user profile for the given post
   */
  public function get_by_post(
    int|WP_Post|null|UserProfile $post = null,
    array $opts = []
  ): ?UserProfile {
    if ($post instanceof UserProfile) {
      $post = $post->id;
    }
    $profile_post = get_post($post);
    if ($profile_post->post_type !== UserProfile::get_post_type()) {
      return null;
    }
    $user = get_user_by('id', $profile_post->post_author);
    return new UserProfile([
      'id' => $profile_post->ID,
      'title' => $profile_post->post_title,
      'author' => $profile_post->post_author,
      'status' => $profile_post->post_status,
      'published_date' => get_post_datetime($profile_post->ID, 'data', 'utc'),
      'slug' => $profile_post->post_name,
      'author_display_name' => $user->display_name,
      'thumbnail_url' => get_the_post_thumbnail_url($profile_post),
      'url' => get_permalink($profile_post),
      'wp_user' => $user,
    ]);
  }

  public function get_by_user(int|WP_User|null $user = null): ?UserProfile {
    if (!$user) {
      $user = wp_get_current_user();
    }
    if (is_int($user)) {
      $user = get_user_by('id', $user);
    }
    if (!$user) {
      return null;
    }
    $profile = $this->profiles_from_query(
      new WP_Query([
        'post_type' => UserProfile::get_post_type(),
        'author' => $user->ID,
        'posts_per_page' => 1,
      ])
    );
    if (count($profile) === 0) {
      return new UserProfile([
        'wp_user' => $user,
      ]);
    }
    return $profile[0];
  }

  public function profiles_from_query(WP_Query $query): array {
    $posts = $query->posts;
    $profiles = [];
    foreach ($posts as $post) {
      $profile = $this->get_by_post($post);
      if ($profile) {
        $profiles[] = $profile;
      }
    }
    return $profiles;
  }

  public function add(UserProfile $profile): UserProfile {
    $post_id = $this->insert_post($profile, true);
    if (!$post_id || is_wp_error($post_id)) {
      throw new Exception('Failed to insert post');
    }

    $profile = $this->get_by_post($post_id);
    return $profile;
  }
}
