<?php

namespace WStrategies\BMB\Features\Notifications\Push;

use WStrategies\BMB\Includes\Repository\CustomTableNames;
use WStrategies\BMB\Includes\Repository\RepositoryBase;
use WStrategies\BMB\Features\Notifications\Push\Exceptions\TokenDatabaseException;
use WStrategies\BMB\Includes\Repository\Exceptions\RepositoryCreateException;
use WStrategies\BMB\Includes\Repository\Exceptions\RepositoryReadException;
use WStrategies\BMB\Includes\Repository\Exceptions\RepositoryUpdateException;
use WStrategies\BMB\Includes\Repository\Exceptions\RepositoryDeleteException;

/**
 * Repository for managing FCM tokens in the database.
 *
 * Handles CRUD operations for FCM tokens and maintains the custom database table.
 */
class FCMTokenRepo extends RepositoryBase {
  /**
   * Create FCMToken model from database row.
   *
   * @param array $row Database row
   * @return FCMToken
   */
  protected function create_model(array $row): FCMToken {
    return new FCMToken($row);
  }

  /**
   * Get field definitions for validation and types.
   *
   * @return array Field definitions
   */
  protected function get_field_definitions(): array {
    return [
      'id' => [
        'type' => '%d',
        'required' => false,
        'searchable' => true,
      ],
      'user_id' => [
        'type' => '%d',
        'required' => true,
        'searchable' => true,
      ],
      'device_id' => [
        'type' => '%s',
        'required' => true,
        'searchable' => true,
      ],
      'token' => [
        'type' => '%s',
        'required' => true,
        'searchable' => true,
        'updateable' => true,
      ],
      'device_type' => [
        'type' => '%s',
        'required' => true,
      ],
      'device_name' => [
        'type' => '%s',
        'required' => false,
        'updateable' => true,
      ],
      'app_version' => [
        'type' => '%s',
        'required' => false,
        'updateable' => true,
      ],
      'last_used_at' => [
        'type' => '%s',
        'required' => false,
        'updateable' => true,
        'default' => 'CURRENT_TIMESTAMP',
      ],
      'created_at' => [
        'type' => '%s',
        'required' => false,
        'default' => 'CURRENT_TIMESTAMP',
      ],
    ];
  }

  /**
   * Retrieves tokens based on search criteria.
   *
   * @param array $args Search criteria
   * @return FCMToken|FCMToken[]|null
   * @throws TokenDatabaseException
   */
  public function get($args = []): FCMToken|array|null {
    try {
      return parent::get($args);
    } catch (RepositoryReadException $e) {
      throw new TokenDatabaseException($e->getMessage(), 0, $e);
    }
  }

  /**
   * Adds a new FCM token.
   *
   * @param FCMToken $token Token to add
   * @return FCMToken|null
   * @throws TokenDatabaseException
   */
  public function add(FCMToken $token): ?FCMToken {
    try {
      return $this->insert([
        'user_id' => $token->user_id,
        'device_id' => $token->device_id,
        'token' => $token->token,
        'device_type' => $token->device_type,
        'device_name' => $token->device_name,
        'app_version' => $token->app_version,
      ]);
    } catch (RepositoryCreateException $e) {
      throw new TokenDatabaseException($e->getMessage(), 0, $e);
    }
  }

  /**
   * Updates device information including token, name and app version.
   *
   * @param int $id Device ID
   * @param array $fields Fields to update
   * @return FCMToken|null
   * @throws TokenDatabaseException
   */
  public function update_token(int $id, array $fields = []): ?FCMToken {
    try {
      // Always update last_used_at when updating token
      $fields['last_used_at'] = current_time('mysql');
      return $this->update($id, $fields);
    } catch (RepositoryUpdateException $e) {
      throw new TokenDatabaseException($e->getMessage(), 0, $e);
    }
  }

  /**
   * Delete a token.
   *
   * @param int $id Token ID
   * @return bool
   * @throws TokenDatabaseException
   */
  public function delete(int $id): bool {
    try {
      return parent::delete($id);
    } catch (RepositoryDeleteException $e) {
      throw new TokenDatabaseException($e->getMessage(), 0, $e);
    }
  }

  /**
   * Deletes tokens that haven't been used in the specified number of days.
   *
   * @param int $days_threshold Number of days of inactivity before deletion
   * @return int Number of tokens deleted
   * @throws TokenDatabaseException
   */
  public function delete_inactive_tokens(int $days_threshold = 30): int {
    $sql = $this->wpdb->prepare(
      "DELETE FROM {$this->table_name} WHERE last_used_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
      $days_threshold
    );

    $deleted = $this->wpdb->query($sql);
    if ($this->wpdb->last_error) {
      throw new TokenDatabaseException(
        "Database error deleting inactive tokens: {$this->wpdb->last_error}"
      );
    }

    return $deleted;
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
   * Gets the custom table name.
   *
   * @return string The fully qualified table name.
   */
  public static function table_name(): string {
    return CustomTableNames::table_name('fcm_tokens');
  }

  public static function create_table(): void {
    global $wpdb;
    $table_name = self::table_name();
    $users_table = $wpdb->users;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
      id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      user_id bigint UNSIGNED NOT NULL,
      device_id varchar(255) NOT NULL,
      token varchar(255) NOT NULL,
      device_type enum('ios', 'android') NOT NULL,
      device_name varchar(255),
      app_version varchar(50),
      last_used_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      UNIQUE KEY device_user_unique (device_id, user_id),
      UNIQUE KEY token_unique (token),
      KEY user_id (user_id),
      KEY last_used_at (last_used_at),
      FOREIGN KEY (user_id) REFERENCES {$users_table}(ID) ON DELETE CASCADE
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
  }

  public static function drop_table(): void {
    global $wpdb;
    $table_name = self::table_name();
    $sql = "DROP TABLE IF EXISTS {$table_name}";
    $wpdb->query($sql);
  }
}
