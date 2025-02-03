<?php

namespace WStrategies\BMB\Includes\Service\Serializer;

use WStrategies\BMB\Includes\Service\Serializer\SerializedFieldBuilder\ObjectDataBuilder;
use WStrategies\BMB\Includes\Service\Serializer\SerializedFieldBuilder\SerializedFieldDirector;
use WStrategies\BMB\Includes\Service\Serializer\SerializedFieldBuilder\SerializedFieldOptionsBuilder;
use WStrategies\BMB\Includes\Service\Serializer\SerializedFieldBuilder\SerializerBuilder;

abstract class ApiSerializerBase implements ApiSerializerInterface {
  protected bool $required;
  protected bool $many;
  protected bool $readonly;

  public function __construct($args = []) {
    $this->required = $args['required'] ?? false;
    $this->many = $args['many'] ?? false;
    $this->readonly = $args['readonly'] ?? false;
  }

  public function is_required(): bool {
    return $this->required;
  }

  public function is_many(): bool {
    return $this->many;
  }

  public function is_readonly(): bool {
    return $this->readonly;
  }

  protected function get_object_data(array $serialized): array {
    $data_builder = new ObjectDataBuilder($serialized);
    $director = new SerializedFieldDirector($data_builder);
    $director->build($this->filter_serialized_fields());
    return $data_builder->get_object_data();
  }

  public function serialize(object|null $obj): mixed {
    if (!$obj) {
      return [];
    }
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

  public function get_schema_type(): string {
    return 'object';
  }

  public function get_schema_properties(): array {
    return $this->get_serialized_fields();
  }
}
