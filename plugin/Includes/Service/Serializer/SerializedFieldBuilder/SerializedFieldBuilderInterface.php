<?php

namespace WStrategies\BMB\Includes\Service\Serializer\SerializedFieldBuilder;

interface SerializedFieldBuilderInterface {
  public function build_field(string $field_name, array $options = []);
}
