<?php
require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-user-profile.php';

class UserProfileTest extends WPBB_UnitTestCase {
  public function test_get_post_type() {
    $this->assertEquals('user_profile', Wpbb_UserProfile::get_post_type());
  }

  public function test_constructor() {
    $user = self::factory()->user->create_and_get();
    $profile_post = self::factory()->post->create_and_get([
      'post_type' => Wpbb_UserProfile::get_post_type(),
      'post_author' => $user->ID,
    ]);
    $args = [
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
    ];
    $profile = new Wpbb_UserProfile($args);
    $this->assertInstanceOf(Wpbb_UserProfile::class, $profile);
    $this->assertEquals($profile_post->ID, $profile->id);
    $this->assertEquals($profile_post->post_title, $profile->title);
    $this->assertEquals($profile_post->post_author, $profile->author);
    $this->assertEquals($profile_post->post_status, $profile->status);
    $this->assertEquals(
      get_post_datetime($profile_post->ID, 'data', 'utc'),
      $profile->published_date
    );
    $this->assertEquals($profile_post->post_name, $profile->slug);
    $this->assertEquals($user->display_name, $profile->author_display_name);
    $this->assertEquals(
      get_the_post_thumbnail_url($profile_post),
      $profile->thumbnail_url
    );
    $this->assertEquals(get_permalink($profile_post), $profile->url);
    $this->assertEquals($user, $profile->wp_user);
  }
}
