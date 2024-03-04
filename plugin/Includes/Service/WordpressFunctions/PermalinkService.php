<?php

namespace WStrategies\BMB\Includes\Service\WordpressFunctions;

class PermalinkService {
  public function get_permalink(int $post_id) {
    return get_permalink($post_id);
  }
}
