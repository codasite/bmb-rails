<?php

namespace WStrategies\BMB\Includes\Service\Serializer;

interface ApiSerializerInterface {
  public function deserialize(array $data): object;

  public function serialize(object $obj): array;

  /**
   * Should return and array of field names, optionally mapped to an array of options that specifies how the field should be serialized
   * options are:
   *   serializer: an ApiSerializerInterface instance that will be used to serialize the field
   *   many: a boolean indicating whether the field is an array of objects
   *   required: a boolean indicating whether the field is required
   *   readonly: a boolean indicating whether the field is readonly
   */
  public function get_serialized_fields(): array;

  public function get_readonly_fields(): array;

  public function get_required_fields(): array;
}
