<?php

namespace WStrategies\BMB\Features\MobileApp;

class RequestService {
  public const MOBILE_APP_USER_AGENT = 'BackMyBracket-MobileApp';

  public function is_mobile_app_request(): bool {
    return !empty($_SERVER['HTTP_USER_AGENT']) &&
      $_SERVER['HTTP_USER_AGENT'] === self::MOBILE_APP_USER_AGENT;
  }
}
