<?php

namespace WStrategies\BMB\Includes\Service\Serializer\SerializedFieldBuilder;

use WStrategies\BMB\Includes\Service\Serializer\ApiSerializerInterface;

class SerializedFieldDirector {
  private SerializedFieldBuilderInterface $builder;

  public function __construct(SerializedFieldBuilderInterface $builder) {
    $this->builder = $builder;
  }

  public function build(array $serialized_fields): void {
    foreach ($serialized_fields as $key => $value) {
      if (is_string($value)) {
        $this->builder->build_field($value);
      } elseif (is_array($value)) {
        $this->builder->build_field($key, $value);
      } elseif ($value instanceof ApiSerializerInterface) {
        $this->builder->build_field($key, [
          'serializer' => $value,
          'many' => $value->is_many(),
          'required' => $value->is_required(),
          'readonly' => $value->is_readonly(),
        ]);
      }
    }
  }
}
