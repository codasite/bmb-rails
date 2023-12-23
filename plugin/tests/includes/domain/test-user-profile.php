<?php

use WStrategies\BMB\Includes\Domain\UserProfile;

class UserProfileTest extends WPBB_UnitTestCase {
  public function test_get_post_type() {
    $this->assertEquals('user_profile', UserProfile::get_post_type());
  }

  public function test_constructor() {
    $user = self::factory()->user->create_and_get();
    $profile_post = self::factory()->post->create_and_get([
      'post_type' => UserProfile::get_post_type(),
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
    $profile = new UserProfile($args);
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

  public function test_constructor_no_post_data() {
    $user = self::factory()->user->create_and_get();
    $args = [
      'author_display_name' => $user->display_name,
      'wp_user' => $user,
    ];
    $profile = new UserProfile($args);
    $this->assertInstanceOf(UserProfile::class, $profile);
    $this->assertEquals($user->display_name, $profile->author_display_name);
    $this->assertEquals($user, $profile->wp_user);
  }

  public function test_get_num_plays() {
    $user = self::factory()->user->create_and_get();
    $user2 = self::factory()->user->create_and_get();
    $bracket = $this->create_bracket();
    $play1 = $this->create_play([
      'author' => $user->ID,
      'bracket_id' => $bracket->id,
    ]);
    $play2 = $this->create_play([
      'author' => $user->ID,
      'bracket_id' => $bracket->id,
    ]);
    $play3 = $this->create_play([
      'author' => $user2->ID,
      'bracket_id' => $bracket->id,
    ]);
    $profile = new UserProfile(['wp_user' => $user]);

    $this->assertEquals(2, $profile->get_num_plays());
  }

  public function test_get_num_wins() {
    $user = self::factory()->user->create_and_get();
    $user2 = self::factory()->user->create_and_get();
    $bracket = $this->create_bracket();
    $play1 = $this->create_play([
      'author' => $user->ID,
      'bracket_id' => $bracket->id,
      'is_winner' => true,
    ]);
    $play2 = $this->create_play([
      'author' => $user->ID,
      'bracket_id' => $bracket->id,
    ]);
    $play3 = $this->create_play([
      'author' => $user2->ID,
      'bracket_id' => $bracket->id,
      'is_winner' => true,
    ]);
    $profile = new UserProfile(['wp_user' => $user]);

    $this->assertEquals(1, $profile->get_tournament_wins());
  }

  public function test_get_num_bmb_wins() {
    $user = self::factory()->user->create_and_get();
    $bracket1 = $this->create_bracket();
    $bracket2 = $this->create_bracket();
    $play1 = $this->create_play([
      'author' => $user->ID,
      'bracket_id' => $bracket1->id,
      'is_winner' => true,
      'bmb_official' => true,
    ]);
    $play2 = $this->create_play([
      'author' => $user->ID,
      'bracket_id' => $bracket2->id,
    ]);
    $play3 = $this->create_play([
      'author' => $user->ID,
      'bracket_id' => $bracket2->id,
      'bmb_official' => true,
    ]);
    $profile = new UserProfile(['wp_user' => $user]);

    $this->assertEquals(1, $profile->get_bmb_tournament_wins());
  }

  public function test_get_num_tournament_wins_excludes_buster_plays() {
    $user = self::factory()->user->create_and_get();
    $bracket = $this->create_bracket();
    $play1 = $this->create_play([
      'author' => $user->ID,
      'bracket_id' => $bracket->id,
      'is_winner' => true,
    ]);
    $play2 = $this->create_play([
      'author' => $user->ID,
      'bracket_id' => $bracket->id,
      'is_winner' => true,
      'busted_id' => $play1->id,
    ]);
    $profile = new UserProfile(['wp_user' => $user]);

    $this->assertEquals(1, $profile->get_tournament_wins());
  }
}
