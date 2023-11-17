<?php

require_once WPBB_PLUGIN_DIR . 'tests/unittest-base.php';
require_once WPBB_PLUGIN_DIR . 'includes/domain/class-wpbb-user-profile.php';
require_once WPBB_PLUGIN_DIR .
  'includes/repository/class-wpbb-user-profile-repo.php';

class ProfileRepoTest extends WPBB_UnitTestCase {
  private $profile_repo;

  public function set_up() {
    parent::set_up();

    $this->profile_repo = new Wpbb_UserProfileRepo();
  }

  public function test_get_by_post() {
    $user = self::factory()->user->create_and_get();
    $profile_post = self::factory()->post->create_and_get([
      'post_type' => Wpbb_UserProfile::get_post_type(),
      'post_author' => $user->ID,
    ]);
    $profile = $this->profile_repo->get_by_post($profile_post);
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

  public function test_get_by_user() {
    $user = self::factory()->user->create_and_get();
    $profile_post = self::factory()->post->create_and_get([
      'post_type' => Wpbb_UserProfile::get_post_type(),
      'post_author' => $user->ID,
    ]);
    $profile = $this->profile_repo->get_by_user($user);
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

  public function test_add() {
    $user = self::factory()->user->create_and_get();
    $profile = new Wpbb_UserProfile([
      'title' => 'Test Profile',
      'status' => 'publish',
      'author' => $user->ID,
    ]);

    $profile = $this->profile_repo->add($profile);

    $this->assertNotNull($profile->id);
    $this->assertEquals('Test Profile', $profile->title);
    $this->assertEquals('publish', $profile->status);
    $this->assertEquals($user->ID, $profile->author);
  }
}
