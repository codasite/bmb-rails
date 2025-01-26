<?php

namespace WStrategies\BMB\Includes\Repository;

/**
 * Generates SQL for creating database tables from field definitions.
 */
class TableSqlGenerator {
  /**
   * Generate CREATE TABLE SQL from field definitions.
   *
   * @param array<string, array{
   *   type: string,           SQL placeholder type (%s, %d, etc.)
   *   sql_type: string,       SQL column type definition
   *   required?: bool,        Whether field is required for inserts
   *   searchable?: bool,      Whether field can be used in WHERE clauses
   *   updateable?: bool,      Whether field can be updated
   *   nullable?: bool,        Whether field can be NULL
   *   default?: mixed,        Default value for field
   *   primary_key?: bool,     Whether field is primary key
   *   index?: bool,           Whether field should be indexed
   *   foreign_key?: array{   Foreign key definition
   *     table: string,        Referenced table
   *     column: string,       Referenced column
   *     on_delete?: string    ON DELETE behavior
   *   }
   * }> $fields Field definitions
   * @return string SQL statement
   */
  public static function generate_table_sql(array $fields): string {
    $sql_parts = [];
    $indexes = [];
    $foreign_keys = [];

    foreach ($fields as $name => $def) {
      $sql = "{$name} {$def['sql_type']}";

      if (!($def['nullable'] ?? true)) {
        $sql .= ' NOT NULL';
      }

      if (isset($def['default'])) {
        $sql .=
          ' DEFAULT ' .
          (is_string($def['default'])
            ? "'{$def['default']}'"
            : $def['default']);
      }

      if ($def['primary_key'] ?? false) {
        $sql .= ' PRIMARY KEY';
      }

      $sql_parts[] = $sql;

      if ($def['index'] ?? false) {
        $indexes[] = "KEY {$name} ({$name})";
      }

      if (isset($def['foreign_key'])) {
        $fk = $def['foreign_key'];
        $on_delete = $fk['on_delete'] ?? 'CASCADE';
        $foreign_keys[] = sprintf(
          'FOREIGN KEY (%s) REFERENCES %s(%s) ON DELETE %s',
          $name,
          $fk['table'],
          $fk['column'],
          $on_delete
        );
      }
    }

    $table_parts = array_merge($sql_parts, $indexes, $foreign_keys);
    return implode(",\n  ", $table_parts);
  }
}
