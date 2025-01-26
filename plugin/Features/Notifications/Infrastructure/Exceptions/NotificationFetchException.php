<?php

namespace WStrategies\BMB\Features\Notifications\Infrastructure\Exceptions;

class NotificationFetchException extends NotificationDatabaseException {
  public function __construct(string $message) {
    parent::__construct($message);
  }
}
