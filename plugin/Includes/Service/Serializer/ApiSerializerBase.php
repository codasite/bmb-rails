<?php

namespace WStrategies\BMB\Includes\Service\Serializer;

use WStrategies\BMB\Includes\Service\Serializer\SerializedFieldBuilder\ObjectDataBuilder;
use WStrategies\BMB\Includes\Service\Serializer\SerializedFieldBuilder\SerializedFieldDirector;
use WStrategies\BMB\Includes\Service\Serializer\SerializedFieldBuilder\SerializerBuilder;

abstract class ApiSerializerBase implements ApiSerializerInterface {
  protected function get_object_data(array $serialized): array {
    $data_builder = new ObjectDataBuilder($serialized);
    $director = new SerializedFieldDirector($data_builder);
    $director->build($this->get_serialized_fields());
    return $data_builder->get_object_data();
  }

  public function serialize(object $obj): array {
    $serializer_builder = new SerializerBuilder($obj);
    $director = new SerializedFieldDirector($serializer_builder);
    $director->build($this->get_serialized_fields());
    return $serializer_builder->get_serialized();
  }

  public function get_serialized_fields(): array {
    return [];
  }

  public function get_readonly_fields(): array {
    return [];
  }
}
