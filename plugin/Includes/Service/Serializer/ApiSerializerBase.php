<?php

namespace WStrategies\BMB\Includes\Service\Serializer;

abstract class ApiSerializerBase implements ApiSerializerInterface {
  public function serialize(object $obj): array {
    $serialized = [];
    foreach ($this->get_serialized_fields() as $field => $value) {
      if (is_string($value)) {
        $serialized[$value] = $obj->$value;
      } else {
        $serialized[$field] = $this->serialize_field($obj, $field, $value);
      }
    }
    return $serialized;
  }

  private function serialize_field(
    object $obj,
    string $field,
    array $value
  ): array {
    $serializer = $value['serializer'];
    $many = $value['many'] ?? false;

    if ($many) {
      $serialized_items = [];
      foreach ($obj->$field as $item) {
        $serialized_items[] = $serializer->serialize($item);
      }
      return $serialized_items;
    }

    return $serializer->serialize($obj->$field);
  }

  public function get_serialized_fields(): array {
    return [];
  }

  public function get_readonly_fields(): array {
    return [];
  }
}
