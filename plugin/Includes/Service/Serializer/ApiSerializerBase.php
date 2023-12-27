<?php

namespace WStrategies\BMB\Includes\Service\Serializer;

use WStrategies\BMB\Includes\Service\Serializer\SerializedFieldBuilder\SerializedFieldDirector;
use WStrategies\BMB\Includes\Service\Serializer\SerializedFieldBuilder\SerializerBuilder;

abstract class ApiSerializerBase implements ApiSerializerInterface {
  protected function get_object_data(array $serialized): array {
    $data = [];
    foreach ($this->get_serialized_fields() as $field => $value) {
      if (is_string($value)) {
        $data[$value] = $serialized[$value] ?? null;
      } else {
        $data[$field] = $this->get_field_data($serialized, $field, $value);
      }
    }
    return $data;
  }

  private function get_field_data(
    array $serialized,
    string $field,
    array $value
  ) {
    $serializer = $value['serializer'];
    $many = $value['many'] ?? false;

    if (!isset($serialized[$field])) {
      return null;
    }

    if ($many) {
      $data = [];
      foreach ($serialized[$field] as $item) {
        $data[] = $serializer->deserialize($item);
      }
      return $data;
    }

    return $serializer->get_object_data($serialized[$field]);
  }

  public function serialize(object $obj): array {
    $serializer_builder = new SerializerBuilder($obj);
    $director = new SerializedFieldDirector($serializer_builder);
    $director->build($this->get_serialized_fields());
    return $serializer_builder->get_serialized();
  }

  // public function serialize(object $obj): array {
  //   $serialized = [];
  //   foreach ($this->get_serialized_fields() as $field => $value) {
  //     if (is_string($value)) {
  //       $serialized[$value] = $obj->$value;
  //     } else {
  //       $serialized[$field] = $this->serialize_field($obj, $field, $value);
  //     }
  //   }
  //   return $serialized;
  // }

  private function serialize_field(
    object $obj,
    string $field,
    array $value
  ): array {
    $serializer = $value['serializer'];
    $many = $value['many'] ?? false;

    if (!isset($obj->$field)) {
      return [];
    }

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
