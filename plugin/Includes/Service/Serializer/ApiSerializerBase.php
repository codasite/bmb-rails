<?php

namespace WStrategies\BMB\Includes\Service\Serializer;

abstract class ApiSerializerBase implements ApiSerializerInterface {
  public function serialize(object $obj): array {
    $serialized = [];
    foreach ($this->get_serialized_fields() as $field) {
      $serialized[$field] = $obj->$field;
    }
    return $serialized;
  }

  // public function deserialize(array $data): object;

  public function get_serialized_fields(): array {
    return [];
  }
}
