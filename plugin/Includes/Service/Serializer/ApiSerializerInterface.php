<?php

namespace WStrategies\BMB\Includes\Service\Serializer;

interface ApiSerializerInterface {
  public function serialize(object $obj): array;

  public function get_serialized_fields(): array;

  public function get_readonly_fields(): array;
}
