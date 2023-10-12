<?php
require_once plugin_dir_path(dirname(__FILE__)) .
  'domain/class-wpbb-custom-post-interface.php';
require_once plugin_dir_path(dirname(__FILE__)) .
  'service/class-wpbb-slug-service.php';

abstract class Wpbb_CustomPostRepoBase
{
  /**
   * @var Wpbb_Slug_Service
   */
  private $slug_service;

  public function __construct()
  {
    $this->slug_service = new Wpbb_Slug_Service();
  }

  protected function insert_post(
    Wpbb_CustomPostInterface $post,
    $wp_error = false,
    $random_slug = false
  ): int {
    $post_data = $post->get_post_data();
    if ($random_slug) {
      $post_data['post_name'] = $this->slug_service->generate();
    }
    $post_id = wp_insert_post($post_data, $wp_error);

    if (0 === $post_id || $post_id instanceof WP_Error) {
      return $post_id;
    }

    // insert post metadata
    foreach ($post->get_post_meta() as $key => $value) {
      update_post_meta($post_id, $key, $value);
    }
    return $post_id;
  }

  protected function update_post(
    Wpbb_CustomPostInterface $post,
    $wp_error = false
  ): int {
    $post_id = wp_update_post($post->get_update_post_data(), $wp_error);

    if (0 === $post_id || $post_id instanceof WP_Error) {
      return $post_id;
    }

    // update post metadata
    foreach ($post->get_update_post_meta() as $key => $value) {
      update_post_meta($post_id, $key, $value);
    }
    return $post_id;
  }

  public function delete_post(int $id, $force = false): bool
  {
    $result = wp_delete_post($id, $force);
    return $result !== false;
  }
}
