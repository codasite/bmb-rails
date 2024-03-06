<?php
namespace WStrategies\BMB\tests\integration\Includes\repository;


use WStrategies\BMB\Includes\Domain\UserProfile;
use WStrategies\BMB\Includes\Repository\UserProfileRepo;

class ProfileRepoTest extends WPBB_UnitTestCase {
  private $profile_repo;

  public function set_up(): void {
    parent::set_up();

    $this->profile_repo = new UserProfileRepo();
  }

  public function test_get_by_post() {
    $user = self::factory()->user->create_and_get();
    $profile_post = $this->create_post([
      'post_type' => UserProfile::get_post_type(),
      'post_author' => $user->ID,
    ]);
    $profile = $this->profile_repo->get_by_post($profile_post);
    $this->assertInstanceOf(UserProfile::class, $profile);
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

  public function test_get_by_user_with_user() {
    $user = self::factory()->user->create_and_get();
    $profile_post = $this->create_post([
      'post_type' => UserProfile::get_post_type(),
      'post_author' => $user->ID,
    ]);
    $profile = $this->profile_repo->get_by_user($user);
    $this->assertInstanceOf(UserProfile::class, $profile);
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

  public function test_get_by_user_current_user() {
    $user = self::factory()->user->create_and_get();
    $profile_post = $this->create_post([
      'post_type' => UserProfile::get_post_type(),
      'post_author' => $user->ID,
    ]);
    wp_set_current_user($user->ID);
    $profile = $this->profile_repo->get_by_user();
    $this->assertInstanceOf(UserProfile::class, $profile);
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
    $profile = new UserProfile([
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

  public function test_get_by_user_no_post() {
    $user = self::factory()->user->create_and_get();
    $profile = $this->profile_repo->get_by_user($user);
    $this->assertInstanceOf(UserProfile::class, $profile);
    $this->assertNull($profile->id);
    $this->assertEquals($profile->wp_user, $user);
  }
}
