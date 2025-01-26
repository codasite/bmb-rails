<?php

namespace WStrategies\BMB\Includes\Repository\Exceptions;

class RepositoryUpdateException extends RepositoryException {
  public function __construct(
    string $message = 'Error updating record',
    ?\Throwable $previous = null
  ) {
    parent::__construct($message, 0, $previous);
  }
}
