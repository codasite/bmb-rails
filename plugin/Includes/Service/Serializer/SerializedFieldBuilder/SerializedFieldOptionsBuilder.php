<?php

namespace WStrategies\BMB\Includes\Service\Serializer\SerializedFieldBuilder;

class SerializedFieldOptionsBuilder implements SerializedFieldBuilderInterface {
  private array $fields = [];
  private array $readonly_fields;
  private array $required_fields;
  public function __construct(
    array $readonly_fields = [],
    array $required_fields = []
  ) {
    $this->readonly_fields = $readonly_fields;
    $this->required_fields = $required_fields;
  }
  public function build_field(string $field_name, array $options = []) {
    if (isset($this->readonly_fields[$field_name])) {
      $options['readonly'] = true;
    }

    if (isset($this->required_fields[$field_name])) {
      $options['required'] = true;
    }

    $this->fields[$field_name] = $options;
  }
  public function get_fields() {
    return $this->fields;
  }
}
