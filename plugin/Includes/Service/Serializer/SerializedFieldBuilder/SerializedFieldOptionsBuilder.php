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
    // var_dump($readonly_fields);
    $this->readonly_fields = $readonly_fields;
    $this->required_fields = $required_fields;
  }
  public function build_field(string $field_name, array $options = []) {
    if (in_array($field_name, $this->readonly_fields)) {
      $options['readonly'] = true;
    }

    if (in_array($field_name, $this->required_fields)) {
      $options['required'] = true;
    }

    $this->fields[$field_name] = $options;
  }
  public function get_fields() {
    return $this->fields;
  }
}
