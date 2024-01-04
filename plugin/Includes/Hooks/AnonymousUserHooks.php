<?php
namespace WStrategies\BMB\Includes\Hooks;

use WP_Post;
use WP_User;
use WStrategies\BMB\Includes\Loader;
use WStrategies\BMB\Includes\Utils;

class AnonymousUserHooks implements HooksInterface {
  private Utils $utils;

  /**
   * @param array<string, mixed> $opts
   * @return void
   */
  public function __construct($opts = []) {
    if (isset($opts['utils']) && $opts['utils'] instanceof Utils) {
      $this->utils = $opts['utils'];
    } else {
      $this->utils = new Utils();
    }
  }

  public function load(Loader $loader): void {
    $loader->add_action(
      'wp_login',
      [$this, 'link_anonymous_bracket_to_user_on_login'],
      10,
      2
    );
    $loader->add_action(
      'user_register',
      [$this, 'link_anonymous_bracket_to_user_on_register'],
      10,
      1
    );
    $loader->add_action(
      'wp_login',
      [$this, 'link_anonymous_play_to_user_on_login'],
      10,
      2
    );
    $loader->add_action(
      'user_register',
      [$this, 'link_anonymous_play_to_user_on_register'],
      10,
      1
    );
    $loader->add_action(
      'wpbb_after_play_printed',
      [$this, 'link_anonymous_printed_play_to_user'],
      10,
      2
    );
  }

  /**
   * this function gets hooked to the 'wp_login' action
   */
  public function link_anonymous_bracket_to_user_on_login(
    string $user_login,
    WP_User $user
  ): void {
    $this->link_anonymous_post_to_user_from_cookie(
      $user->ID,
      'wpbb_anonymous_bracket_id',
      'wpbb_anonymous_bracket_key'
    );
  }

  public function link_anonymous_bracket_to_user_on_register(
    int $user_id
  ): void {
    $this->link_anonymous_post_to_user_from_cookie(
      $user_id,
      'wpbb_anonymous_bracket_id',
      'wpbb_anonymous_bracket_key'
    );
  }

  public function link_anonymous_play_to_user_on_login(
    string $user_login,
    WP_User $user
  ): void {
    $this->link_anonymous_post_to_user_from_cookie(
      $user->ID,
      'play_id',
      'wpbb_anonymous_play_key'
    );
  }

  public function link_anonymous_play_to_user_on_register(int $user_id): void {
    $this->link_anonymous_post_to_user_from_cookie(
      $user_id,
      'play_id',
      'wpbb_anonymous_play_key'
    );
  }

  // This is needed in case a user prints a play without logging in
  public function link_anonymous_printed_play_to_user(
    int $play_id,
    int $user_id
  ): void {
    $this->link_anonymous_post_to_user($play_id, $user_id);
  }

  public function link_anonymous_post_to_user_from_cookie(
    int $user_id,
    string $cookie_id_name,
    string $cookie_verify_key_name
  ): void {
    $post_id = $this->utils->pop_cookie($cookie_id_name);
    $cookie_key = $this->utils->pop_cookie($cookie_verify_key_name);
    $post_meta = get_post_meta($post_id, $cookie_verify_key_name);
    if (isset($post_meta) && is_array($post_meta) && !empty($post_meta)) {
      $meta_key = $post_meta[0];
    } else {
      return;
    }

    if ($cookie_key !== $meta_key) {
      return;
    }

    $this->link_anonymous_post_to_user($post_id, $user_id);
  }

  public function link_anonymous_post_to_user(
    int $post_id,
    int $user_id
  ): void {
    $post = get_post($post_id);
    if ($post instanceof WP_Post && (int) $post->post_author === 0) {
      wp_update_post([
        'ID' => $post_id,
        'post_author' => $user_id,
      ]);
    }
  }
}
