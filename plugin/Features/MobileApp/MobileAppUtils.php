<?php

namespace WStrategies\BMB\Features\MobileApp;

class MobileAppUtils {
  public const MOBILE_APP_USER_AGENT = 'BackMyBracket-MobileApp';

  public function is_mobile_app_request(): bool {
    return !empty($_SERVER['HTTP_USER_AGENT']) &&
      $_SERVER['HTTP_USER_AGENT'] === self::MOBILE_APP_USER_AGENT;
  }
  public function get_mobile_meta_query(): array {
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
