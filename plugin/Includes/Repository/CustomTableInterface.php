<?php

namespace WStrategies\BMB\Includes\Repository;

interface CustomTableInterface {
  public static function create_table(): void;
  public static function drop_table(): void;
  public static function table_name(): string;
}
