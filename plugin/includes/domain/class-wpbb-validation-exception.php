<?php

class Wpbb_ValidationException extends Exception {
}

/**
 * @throws Wpbb_ValidationException
 */
function validateRequiredFields(array $data, array $requiredFields): void {
  $missingFields = [];

  foreach ($requiredFields as $field) {
    if (!isset($data[$field])) {
      $missingFields[] = $field;
    }
  }

  if (!empty($missingFields)) {
    $errorMessages = implode(', ', $missingFields) . ' is required';
    throw new Wpbb_ValidationException($errorMessages);
  }
}
