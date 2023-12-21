<?php

namespace WStrategies\BMB\Includes\Repository;

class CustomTableNames {
  public static function table_name(string $table_name): string {
    global $wpdb;
    return $wpdb->prefix . self::custom_table_prefix() . $table_name;
  }

  public static function custom_table_prefix(): string {
    return WPBB_DB_PREFIX;
  }
}
