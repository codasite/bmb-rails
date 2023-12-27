<?php

namespace WStrategies\BMB\Includes\Service\Serializer\SerializedFieldBuilder;
use WStrategies\BMB\Includes\Service\Serializer\ApiSerializerInterface;

class ObjectDataBuilder extends SerializedFieldBuilderBase {
  private array $obj_data;
  private array $serialized;

  public function __construct(array $serialized) {
    $this->serialized = $serialized;
    $this->obj_data = [];
  }

  public function build_string_field(string $field_name): void {
    if (isset($this->serialized[$field_name])) {
      $this->obj_data[$field_name] = $this->serialized[$field_name];
    }
  }

  public function build_serializer_field(
    string $field_name,
    ApiSerializerInterface $serializer
  ) {
    if (isset($this->serialized[$field_name])) {
      $this->obj_data[$field_name] = $serializer->deserialize(
        $this->serialized[$field_name]
      );
    }
  }

  public function build_serializer_field_many(
    string $field_name,
    ApiSerializerInterface $serializer
  ) {
    if (isset($this->serialized[$field_name])) {
      $data = [];
      foreach ($this->serialized[$field_name] as $item) {
        $data[] = $serializer->deserialize($item);
      }
      $this->obj_data[$field_name] = $data;
    }
  }

  public function get_object_data(): array {
    return $this->obj_data;
  }
}
