<?php
namespace WStrategies\BMB\Includes\Repository;

use WP_Error;
use WStrategies\BMB\Includes\Domain\CustomPostInterface;
use WStrategies\BMB\Includes\Service\SlugService;

abstract class CustomPostRepoBase {
  /**
   * @var SlugService
   */
  private $slug_service;

  public function __construct() {
    $this->slug_service = new SlugService();
  }

  protected function insert_post(
    CustomPostInterface $post,
    $wp_error = false,
    $random_slug = false
  ): int {
    $post_data = $post->get_post_data();
    if ($random_slug && empty($post_data['post_name'])) {
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
    CustomPostInterface $post,
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

  public function delete_post(int $id, $force = false): bool {
    if ($force) {
      $result = wp_delete_post($id, $force);
    } else {
      $result = wp_trash_post($id);
    }
    return $result !== false;
  }
}
