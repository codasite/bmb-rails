<?php

namespace WStrategies\BMB\Includes\Repository\Exceptions;

class RepositoryException extends \Exception {
  public function __construct(
    string $message = '',
    int $code = 0,
    ?\Throwable $previous = null
  ) {
    parent::__construct($message, $code, $previous);
  }
}
