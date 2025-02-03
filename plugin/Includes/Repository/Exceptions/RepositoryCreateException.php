<?php

namespace WStrategies\BMB\Includes\Repository\Exceptions;

class RepositoryCreateException extends RepositoryException {
  public function __construct(
    string $message = 'Error creating record',
    ?\Throwable $previous = null
  ) {
    parent::__construct($message, 0, $previous);
  }
}
