<?php

namespace WStrategies\BMB\Features\Notifications\Infrastructure\Exceptions;

class NotificationDatabaseException extends \Exception {
  public function __construct(string $message) {
    parent::__construct($message);
  }
}
