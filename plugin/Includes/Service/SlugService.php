<?php
namespace WStrategies\BMB\Includes\Service;

class SlugService {
  public function generate() {
    do {
      $slug = wp_generate_password(8, false);
    } while ($this->slug_exists($slug));

    return $slug;
  }

  private function slug_exists($slug) {
    global $wpdb;
    $query = "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_name = %s";
    $count = $wpdb->get_var($wpdb->prepare($query, $slug));
    return $count > 0;
  }
}
