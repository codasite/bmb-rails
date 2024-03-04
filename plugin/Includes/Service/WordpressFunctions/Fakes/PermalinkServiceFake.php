<?php

namespace WStrategies\BMB\Includes\Service\WordpressFunctions\Fakes;

use WStrategies\BMB\Includes\Service\WordpressFunctions\PermalinkService;

class PermalinkServiceFake extends PermalinkService {
  public function __construct(private string $permalink = 'http://test.com') {
  }

  public function get_permalink(int $post_id) {
    return $this->permalink;
  }
}
