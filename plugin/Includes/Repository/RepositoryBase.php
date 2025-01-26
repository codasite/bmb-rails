<?php

namespace WStrategies\BMB\Includes\Repository;

use WStrategies\BMB\Includes\Repository\Exceptions\RepositoryCreateException;
use WStrategies\BMB\Includes\Repository\Exceptions\RepositoryReadException;
use WStrategies\BMB\Includes\Repository\Exceptions\RepositoryUpdateException;
use WStrategies\BMB\Includes\Repository\Exceptions\RepositoryDeleteException;

/**
 * Base repository class providing common database operations.
 */
abstract class RepositoryBase implements CustomTableInterface {
  protected \wpdb $wpdb;
  protected string $table_name;

  /**
   * Initialize the repository.
   */
  public function __construct() {
    global $wpdb;
    $this->wpdb = $wpdb;
    $this->table_name = static::table_name();
  }

  /**
   * Get the table name for this repository.
   *
   * @return string The fully qualified table name
   */
  abstract public static function table_name(): string;

  /**
   * Create a model instance from database row.
   *
   * @param array $row Database row
   * @return mixed Model instance
   */
  abstract protected function create_model(array $row);

  /**
   * Get field definitions for validation and types.
   *
   * @return array<string, array{
   *   type: string,        SQL placeholder type (%s, %d, etc.)
   *   required?: bool,     Whether field is required for inserts
   *   searchable?: bool,   Whether field can be used in WHERE clauses
   *   updateable?: bool,   Whether field can be updated
   *   default?: mixed,     Default value for field
   * }> Field definitions
   */
  abstract protected function get_field_definitions(): array;

  /**
   * Get fields that can be used in WHERE clauses.
   *
   * @return array<string, string>
   */
  protected function get_searchable_fields(): array {
    $searchable = [];
    foreach ($this->get_field_definitions() as $field => $def) {
      if ($def['searchable'] ?? false) {
        $searchable[$field] = $def['type'];
      }
    }
    return $searchable;
  }

  /**
   * Get fields that can be updated.
   *
   * @return array<string>
   */
  protected function get_updateable_fields(): array {
    $updateable = [];
    foreach ($this->get_field_definitions() as $field => $def) {
      if ($def['updateable'] ?? false) {
        $updateable[] = $field;
      }
    }
    return $updateable;
  }

  /**
   * Get fields that can be inserted with their validation rules.
   *
   * @return array<string, array{
   *   required?: bool,
   *   type: string,
   *   default?: mixed
   * }>
   */
  protected function get_insertable_fields(): array {
    $insertable = [];
    foreach ($this->get_field_definitions() as $field => $def) {
      $insertable[$field] = [
        'type' => $def['type'],
        'required' => $def['required'] ?? false,
      ];
      if (isset($def['default'])) {
        $insertable[$field]['default'] = $def['default'];
      }
    }
    return $insertable;
  }

  /**
   * Get records based on search criteria.
   *
   * @param array $args {
   *     Optional. Arguments for filtering records.
   *     @type int     $id      Record ID.
   *     @type bool    $single  Whether to return a single result.
   *     @type string  $orderby Column to order by.
   *     @type string  $order   Order direction (ASC/DESC).
   * }
   * @return mixed|array|null The found record(s) or null if not found.
   * @throws RepositoryReadException If database error occurs
   */
  protected function get($args = []) {
    $single = $args['single'] ?? false;
    $where = 'WHERE 1=1';
    $params = [];

    // Handle searchable fields
    $searchable_fields = $this->get_searchable_fields();
    foreach ($searchable_fields as $field => $type) {
      if (isset($args[$field])) {
        $where .= " AND {$field} = {$type}";
        $params[] = $args[$field];
      }
    }

    // Handle ordering
    $orderby = isset($args['orderby']) ? $args['orderby'] : 'id';
    $order = isset($args['order']) ? strtoupper($args['order']) : 'ASC';
    $order = in_array($order, ['ASC', 'DESC']) ? $order : 'ASC';

    $query = $this->wpdb->prepare(
      "SELECT * FROM {$this->table_name} {$where} ORDER BY {$orderby} {$order}",
      $params
    );

    print_r($query);

    $results = $this->wpdb->get_results($query, ARRAY_A);
    if ($this->wpdb->last_error) {
      throw new RepositoryReadException(
        "Database error fetching records: {$this->wpdb->last_error}"
      );
    }

    if (empty($results)) {
      return null;
    }

    if ($single) {
      return $this->create_model($results[0]);
    }

    return array_map([$this, 'create_model'], $results);
  }

  /**
   * Insert a new record.
   *
   * @param array $fields Fields to insert
   * @return mixed|null Created model or null on failure
   * @throws RepositoryCreateException If validation fails or database error occurs
   */
  protected function insert(array $fields) {
    $insertable_fields = $this->get_insertable_fields();

    // Check for invalid fields
    $invalid_fields = array_diff(
      array_keys($fields),
      array_keys($insertable_fields)
    );
    if (!empty($invalid_fields)) {
      throw new RepositoryCreateException(
        sprintf(
          'Invalid fields provided for insert: %s. Allowed fields are: %s',
          implode(', ', $invalid_fields),
          implode(', ', array_keys($insertable_fields))
        )
      );
    }

    // Validate required fields
    $missing_fields = [];
    foreach ($insertable_fields as $field => $rules) {
      if (($rules['required'] ?? false) && !isset($fields[$field])) {
        $missing_fields[] = $field;
      }
    }

    if (!empty($missing_fields)) {
      throw new RepositoryCreateException(
        sprintf('Missing required fields: %s', implode(', ', $missing_fields))
      );
    }

    // Build data with defaults
    $data = [];
    foreach ($insertable_fields as $field => $rules) {
      if (isset($fields[$field])) {
        $data[$field] = $fields[$field];
      } elseif (isset($rules['default'])) {
        $data[$field] = $rules['default'];
      }
    }

    $inserted = $this->wpdb->insert($this->table_name, $data);

    if ($this->wpdb->last_error) {
      throw new RepositoryCreateException(
        "Database error inserting record: {$this->wpdb->last_error}"
      );
    }

    if (!$inserted) {
      return null;
    }

    try {
      return $this->get([
        'id' => $this->wpdb->insert_id,
        'single' => true,
      ]);
    } catch (RepositoryReadException $e) {
      throw new RepositoryCreateException(
        "Record created but could not be retrieved: {$e->getMessage()}"
      );
    }
  }

  /**
   * Update an existing record.
   *
   * @param int $id Record ID
   * @param array $fields Fields to update
   * @return mixed|null Updated model or null on failure
   * @throws RepositoryUpdateException If database error occurs or invalid fields provided
   */
  protected function update(int $id, array $fields) {
    $updateable_fields = $this->get_updateable_fields();

    // Check for invalid fields
    $invalid_fields = array_diff(array_keys($fields), $updateable_fields);

    if (!empty($invalid_fields)) {
      throw new RepositoryUpdateException(
        sprintf(
          'Invalid fields provided for update: %s. Allowed fields are: %s',
          implode(', ', $invalid_fields),
          implode(', ', $updateable_fields)
        )
      );
    }

    // Filter to only allowed fields
    $data = array_intersect_key($fields, array_flip($updateable_fields));

    if (empty($data)) {
      throw new RepositoryUpdateException(
        'No valid fields provided for update'
      );
    }

    $updated = $this->wpdb->update($this->table_name, $data, ['id' => $id]);

    if ($this->wpdb->last_error) {
      throw new RepositoryUpdateException(
        "Database error updating record: {$this->wpdb->last_error}"
      );
    }

    if ($updated === false) {
      return null;
    }

    try {
      return $this->get([
        'id' => $id,
        'single' => true,
      ]);
    } catch (RepositoryReadException $e) {
      throw new RepositoryUpdateException(
        "Record updated but could not be retrieved: {$e->getMessage()}"
      );
    }
  }

  /**
   * Delete a record.
   *
   * @param int $id Record ID
   * @return bool Whether deletion was successful
   * @throws RepositoryDeleteException If database error occurs
   */
  protected function delete(int $id): bool {
    $deleted = $this->wpdb->delete($this->table_name, ['id' => $id]);

    if ($this->wpdb->last_error) {
      throw new RepositoryDeleteException(
        "Database error deleting record: {$this->wpdb->last_error}"
      );
    }

    return $deleted !== false;
  }

  /**
   * Suppress database errors
   *
   * @return bool|null Previous value of show_errors
   */
  public function suppress_errors() {
    return $this->wpdb->suppress_errors();
  }

  /**
   * Show database errors
   */
  public function show_errors() {
    $this->wpdb->show_errors();
  }

  /**
   * Drop the database table.
   */
  public static function drop_table(): void {
    global $wpdb;
    $table_name = static::table_name();
    $sql = "DROP TABLE IF EXISTS {$table_name}";
    $wpdb->query($sql);
  }
}
