<?php

namespace WStrategies\BMB\Includes\Service\Serializer\SerializedFieldBuilder;

use WStrategies\BMB\Includes\Service\Serializer\ApiSerializerInterface;

class SerializerBuilder extends SerializedFieldBuilderBase {
  private object $obj;
  private array $serialized;
  public function __construct(object $obj) {
    $this->obj = $obj;
    $this->serialized = [];
  }

  public function build_string_field(string $field_name): void {
    $this->serialized[$field_name] = $this->obj->$field_name;
  }

  public function build_serializer_field(
    string $field_name,
    ApiSerializerInterface $serializer
  ) {
    if (isset($this->obj->$field_name)) {
      $this->serialized[$field_name] = $serializer->serialize(
        $this->obj->$field_name
      );
    }
  }

  public function build_serializer_field_many(
    string $field_name,
    ApiSerializerInterface $serializer
  ) {
    if (isset($this->obj->$field_name)) {
      $serialized_items = [];
      foreach ($this->obj->$field_name as $item) {
        $serialized_items[] = $serializer->serialize($item);
      }
      $this->serialized[$field_name] = $serialized_items;
    }
  }

  public function get_serialized(): array {
    return $this->serialized;
  }
}
