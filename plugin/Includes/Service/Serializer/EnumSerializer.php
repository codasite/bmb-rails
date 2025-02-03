<?php

namespace WStrategies\BMB\Includes\Service\Serializer;

use WStrategies\BMB\Includes\Domain\ValidationException;

class EnumSerializer extends ApiSerializerBase {
  private string $enumClass;

  /**
   * @param string $enumClass The fully qualified class name of the enum
   */
  public function __construct(string $enumClass) {
    $this->enumClass = $enumClass;
  }

  /**
   * Deserialize a string into an enum instance
   *
   * @param array|string $value The data to deserialize
   * @return object The enum instance
   * @throws ValidationException If the enum value is invalid
   */
  public function deserialize(array|string $value): object {
    try {
      return $this->enumClass::from($value);
    } catch (\ValueError $e) {
      throw new ValidationException(
        sprintf('Invalid enum value "%s" for %s', $value, $this->enumClass)
      );
    }
  }

  /**
   * Serialize an enum to its string value
   *
   * @param object|null $obj The enum to serialize
   * @return string The serialized data
   */
  public function serialize(?object $obj): string {
    if (!$obj instanceof \BackedEnum) {
      return '';
    }

    return $obj->value;
  }

  public function get_serialized_fields(): array {
    return [];
  }
}
