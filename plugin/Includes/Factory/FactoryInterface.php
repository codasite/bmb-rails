<?php

namespace WStrategies\BMB\Includes\Factory;

interface FactoryInterface {
  public function create(array $data): object;
  // public function validate(array $data): array;
}
