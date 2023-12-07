<?php

class AdminHooksTest extends WPBB_UnitTestCase {
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
