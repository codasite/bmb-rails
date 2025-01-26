<?php

namespace WStrategies\BMB\Features\Notifications\Infrastructure\Exceptions;

class NotificationUpdateException extends NotificationDatabaseException {
  public function __construct(string $message) {
    parent::__construct($message);
  }
}
