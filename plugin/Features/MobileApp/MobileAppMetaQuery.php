<?php

namespace WStrategies\BMB\Features\MobileApp;

class MobileAppMetaQuery {
  public static function get_mobile_meta_query(): array {
    return [
      'relation' => 'OR',
      [
        'key' => 'bracket_fee',
        'value' => '0',
        'compare' => '=',
      ],
      [
        'key' => 'bracket_fee',
        'compare' => 'NOT EXISTS',
      ],
    ];
  }
}
