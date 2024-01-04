<?php

namespace WStrategies\BMB\Includes\Helpers\Wordpress;

class Navigation {
  public static function get_page_permalink_by_path(string $path): string {
    $page = get_page_by_path($path);
    if ($page instanceof \WP_Post) {
      $page_id = $page->ID;
      $permalink = get_permalink($page_id);
      if ($permalink !== false) {
        return $permalink;
      }
    }
    return '';
  }
}
