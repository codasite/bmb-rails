<?php

namespace WStrategies\BMB\Includes\Service\Serializer\SerializedFieldBuilder;

use WStrategies\BMB\Includes\Domain\ValidationException;
use WStrategies\BMB\Includes\Service\Serializer\ApiSerializerInterface;

class ObjectDataBuilder extends SerializedFieldBuilderBase {
  private array $errors = [];
  private array $obj_data = [];
  private array $serialized;

  public function __construct(array $serialized) {
    $this->serialized = $serialized;
  }

  private function build_simple_field(string $field_name): void {
    if (isset($this->serialized[$field_name])) {
      $this->obj_data[$field_name] = $this->serialized[$field_name];
    }
  }

  private function build_serializer_field(
    string $field_name,
    ApiSerializerInterface $serializer
  ): void {
    if (isset($this->serialized[$field_name])) {
      $this->obj_data[$field_name] = $serializer->deserialize(
        $this->serialized[$field_name]
      );
    }
  }

  private function build_serializer_field_many(
    string $field_name,
    ApiSerializerInterface $serializer
  ): void {
    if (isset($this->serialized[$field_name])) {
      $data = [];
      foreach ($this->serialized[$field_name] as $item) {
        $data[] = $serializer->deserialize($item);
      }
      $this->obj_data[$field_name] = $data;
    }
  }

  public function build_field(string $field_name, array $options = []) {
    list(
      $serializer,
      $many,
      $required,
      $readonly,
    ) = $this->parse_serializer_options($options);

    if ($readonly) {
      return;
    }
    if ($required && !isset($this->serialized[$field_name])) {
      return $this->errors['missing'][] = $field_name;
    }
    if ($serializer) {
      if ($many) {
        $this->build_serializer_field_many($field_name, $serializer);
      } else {
        $this->build_serializer_field($field_name, $serializer);
      }
    } else {
      $this->build_simple_field($field_name);
    }
  }

  public function get_object_data(): array {
    if (!empty($this->errors['missing'])) {
      throw new ValidationException(
        'Missing required fields: ' . implode(', ', $this->errors['missing'])
      );
    }
    return $this->obj_data;
  }
}
