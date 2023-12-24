<?php

namespace WStrategies\BMB\Includes\Service\Serializer;

interface ApiSerializerInterface {
  public function serialize(object $obj): array;

  // public function deserialize(array $data): object;

  public function get_serialized_fields(): array;
}
