<?php

namespace WStrategies\BMB\Includes\Service\Serializer\SerializedFieldBuilder;

class SerializedFieldDirector {
  private SerializedFieldBuilderInterface $builder;

  public function __construct(SerializedFieldBuilderInterface $builder) {
    $this->builder = $builder;
  }

  public function build(array $serialized_fields) {
    foreach ($serialized_fields as $key => $value) {
      if (is_string($value)) {
        $this->builder->build_field($value);
      } elseif (is_array($value)) {
        $this->builder->build_field($key, $value);
      }
    }
  }
}
