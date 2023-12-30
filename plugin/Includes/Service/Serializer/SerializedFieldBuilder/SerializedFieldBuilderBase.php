<?php

namespace WStrategies\BMB\Includes\Service\Serializer\SerializedFieldBuilder;

abstract class SerializedFieldBuilderBase implements
  SerializedFieldBuilderInterface {
  public function build_field(string $field_name, array $options = []) {
  }
  public function parse_serializer_options(array $options): array {
    $serializer = $options['serializer'] ?? null;
    $many = $options['many'] ?? false;
    $required = $options['required'] ?? false;
    $readonly = $options['readonly'] ?? false;
    return [$serializer, $many, $required, $readonly];
  }
}
