<?php

class AdminHooksTest extends WPBB_UnitTestCase {
  public function test_add_upcoming_tag_should_change_status_to_upcoming() {
    $factory = self::factory()->bracket;
    $bracket = $factory->create_and_get([
      'status' => 'publish',
    ]);
    wp_add_post_tags($bracket->id, 'bmb_upcoming');
    $updated_bracket = $factory->get_object_by_id($bracket->id);
    $this->assertEquals('upcoming', $updated_bracket->status);
  }
  public function test_status_remove_upcoming_tag_should_change_status_to_publish() {
    $factory = self::factory()->bracket;
    $bracket = $factory->create_and_get([
      'status' => 'upcoming',
    ]);
    // remove the upcoming tag
    wp_set_post_terms($bracket->id, '', 'post_tag');
    $updated_bracket = $factory->get_object_by_id($bracket->id);
    $this->assertEquals('publish', $updated_bracket->status);
  }
  public function test_change_role_to_vip_should_add_new_user_profile_post() {
    $user = self::factory()->user->create_and_get();
    $user->add_role('bmb_vip');
    $posts = get_posts([
      'post_type' => 'user_profile',
      'author' => $user->ID,
    ]);
    $this->assertEquals(1, count($posts));
    $this->assertEquals($user->ID, $posts[0]->post_author);
    $meta = get_post_meta($posts[0]->ID);
  }

  public function test_add_user_with_bmb_vip_role_should_add_new_user_profile_post() {
    $user = self::factory()->user->create_and_get(['role' => 'bmb_vip']);
    $posts = get_posts([
      'post_type' => 'user_profile',
      'author' => $user->ID,
    ]);
    $this->assertEquals(1, count($posts));
    $this->assertEquals($user->ID, $posts[0]->post_author);
  }

  public function test_remove_vip_role_from_user_should_remove_user_profile_post() {
    $user = self::factory()->user->create_and_get(['role' => 'bmb_vip']);
    $posts = get_posts([
      'post_type' => 'user_profile',
      'author' => $user->ID,
    ]);
    $this->assertEquals(1, count($posts));
    $this->assertEquals($user->ID, $posts[0]->post_author);
    $user->remove_role('bmb_vip');
    $posts = get_posts([
      'post_type' => 'user_profile',
      'author' => $user->ID,
    ]);
    $this->assertEquals(0, count($posts));
  }
}
