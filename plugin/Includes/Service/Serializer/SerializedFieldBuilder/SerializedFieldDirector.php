<?php

namespace WStrategies\BMB\Includes\Service\Serializer\SerializedFieldBuilder;

class SerializedFieldDirector {
  private SerializedFieldBuilderBase $builder;

  public function __construct(SerializedFieldBuilderBase $builder) {
    $this->builder = $builder;
  }

  public function build(array $serialized_fields) {
    foreach ($serialized_fields as $key => $value) {
      if (is_string($value)) {
        $this->builder->build_string_field($value);
      } elseif (is_array($value)) {
        $serializer = $value['serializer'];
        $many = $value['many'] ?? false;
        if ($many) {
          $this->builder->build_serializer_field_many($key, $serializer);
        } else {
          $this->builder->build_serializer_field($key, $serializer);
        }
      }
    }
  }
}
