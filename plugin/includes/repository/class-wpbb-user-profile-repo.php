<?php
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-user-profile.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-custom-post-repo.php';
require_once WPBB_PLUGIN_DIR . 'includes/class-wpbb-utils.php';

class Wpbb_UserProfileRepo extends Wpbb_CustomPostRepoBase {
  /**
   * @var Wpbb_Utils
   */
  private $utils;

  /**
   * @var wpdb
   */
  private $wpdb;

  public function __construct() {
    global $wpdb;
    $this->wpdb = $wpdb;
    $this->utils = new Wpbb_Utils();
    parent::__construct();
  }

  /**
   * Get the user profile for the given post
   */
  public function get_by_post(
    int|WP_Post|null|Wpbb_UserProfile $post = null,
    array $opts = []
  ): ?Wpbb_UserProfile {
    if ($post instanceof Wpbb_UserProfile) {
      $post = $post->id;
    }
    $profile_post = get_post($post);
    if (
      !$profile_post ||
      $profile_post->post_type !== Wpbb_UserProfile::get_post_type()
    ) {
      return null;
    }
    $user = get_user_by('id', $post->post_author);
    return new Wpbb_UserProfile([
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

  public function get_by_user(
    int|WP_User|null $user = null
  ): ?Wpbb_UserProfile {
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
        'post_type' => Wpbb_UserProfile::get_post_type(),
        'author' => $user->ID,
        'posts_per_page' => 1,
      ])
    );
    if (count($profile) === 0) {
      return null;
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
}
