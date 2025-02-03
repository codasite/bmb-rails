<?php

namespace WStrategies\BMB\Includes\Repository\Exceptions;

class RepositoryReadException extends RepositoryException {
  public function __construct(
    string $message = 'Error reading record',
    ?\Throwable $previous = null
  ) {
    parent::__construct($message, 0, $previous);
  }
}
