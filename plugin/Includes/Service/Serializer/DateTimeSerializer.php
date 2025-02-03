<?php

namespace WStrategies\BMB\Includes\Service\Serializer;

use DateTime;
use WStrategies\BMB\Includes\Domain\ValidationException;

class DateTimeSerializer extends ApiSerializerBase {
  /**
   * Deserialize a string into a DateTime object
   *
   * @param array|string $value The data to deserialize
   * @return DateTime
   * @throws ValidationException If the date string is invalid
   */
  public function deserialize(array|string $value): DateTime {
    try {
      return new DateTime($value);
    } catch (\Exception $e) {
      throw new ValidationException('Invalid date format');
    }
  }

  /**
   * Serialize a DateTime object to ISO 8601 string
   *
   * @param DateTime|null $obj The DateTime object to serialize
   * @return string The serialized data
   */
  public function serialize(?object $obj): string {
    if (!$obj instanceof DateTime) {
      return '';
    }

    return $obj->format('c');
  }
}
