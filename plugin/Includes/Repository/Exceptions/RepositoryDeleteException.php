<?php

namespace WStrategies\BMB\Includes\Repository\Exceptions;

class RepositoryDeleteException extends RepositoryException {
  public function __construct(
    string $message = 'Error deleting record',
    ?\Throwable $previous = null
  ) {
    parent::__construct($message, 0, $previous);
  }
}
