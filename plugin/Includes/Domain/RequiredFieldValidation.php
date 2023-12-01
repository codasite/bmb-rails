<?php

namespace WStrategies\BMB\Includes\Domain;

class RequiredFieldValidation {
  /**
   * @throws ValidationException
   */
  public static function validateRequiredFields(
    array $data,
    array $requiredFields
  ): void {
    $missingFields = [];

    foreach ($requiredFields as $field) {
      if (!isset($data[$field])) {
        $missingFields[] = $field;
      }
    }

    if (!empty($missingFields)) {
      $errorMessages = implode(', ', $missingFields) . ' is required';
      throw new ValidationException($errorMessages);
    }
  }
}
