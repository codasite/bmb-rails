<?php

namespace WStrategies\BMB\Includes\Service\Serializer\SerializedFieldBuilder;

use WStrategies\BMB\Includes\Service\Serializer\ApiSerializerInterface;

abstract class SerializedFieldBuilderBase {
  public function build_string_field(string $field_name): void {
  }

  public function build_serializer_field(
    string $field_name,
    ApiSerializerInterface $serializer
  ) {
  }

  public function build_serializer_field_many(
    string $field_name,
    ApiSerializerInterface $serializer
  ) {
  }
}
