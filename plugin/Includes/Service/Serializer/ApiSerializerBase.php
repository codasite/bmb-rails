<?php

namespace WStrategies\BMB\Includes\Service\Serializer;

use WStrategies\BMB\Includes\Service\Serializer\SerializedFieldBuilder\ObjectDataBuilder;
use WStrategies\BMB\Includes\Service\Serializer\SerializedFieldBuilder\SerializedFieldDirector;
use WStrategies\BMB\Includes\Service\Serializer\SerializedFieldBuilder\SerializedFieldOptionsBuilder;
use WStrategies\BMB\Includes\Service\Serializer\SerializedFieldBuilder\SerializerBuilder;

abstract class ApiSerializerBase implements ApiSerializerInterface {
  protected function get_object_data(array $serialized): array {
    $data_builder = new ObjectDataBuilder($serialized);
    $director = new SerializedFieldDirector($data_builder);
    $director->build($this->filter_serialized_fields());
    return $data_builder->get_object_data();
  }

  public function serialize(object $obj): array {
    $serializer_builder = new SerializerBuilder($obj);
    $director = new SerializedFieldDirector($serializer_builder);
    $director->build($this->filter_serialized_fields());
    return $serializer_builder->get_serialized();
  }

  private function filter_serialized_fields(): array {
    $options_builder = new SerializedFieldOptionsBuilder(
      $this->get_readonly_fields(),
      $this->get_required_fields()
    );
    $director = new SerializedFieldDirector($options_builder);
    $director->build($this->get_serialized_fields());
    $fields = $options_builder->get_fields();
    // var_dump($fields);
    return $options_builder->get_fields();
  }

  public function get_serialized_fields(): array {
    return [];
  }

  public function get_readonly_fields(): array {
    return [];
  }

  public function get_required_fields(): array {
    return [];
  }
}
