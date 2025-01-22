<?php

namespace WStrategies\BMB\Features\Notifications\Push;

use WStrategies\BMB\Includes\Repository\CustomTableInterface;
use WStrategies\BMB\Includes\Repository\CustomTableNames;
use WStrategies\BMB\Features\Notifications\Push\Exceptions\TokenDatabaseException;

/**
 * Repository for managing FCM tokens in the database.
 *
 * Handles CRUD operations for FCM tokens and maintains the custom database table.
 */
class FCMTokenRepo implements CustomTableInterface {
  /** @var \wpdb WordPress database instance */
  public \wpdb $wpdb;

  /**
   * Initializes the repository.
   */
  function __construct() {
    global $wpdb;
    $this->wpdb = $wpdb;
  }

  /**
   * Retrieves tokens based on search criteria.
   *
   * @param array $args {
   *     Optional. Arguments for filtering tokens.
   *     @type int     $id        Token ID.
   *     @type int     $user_id   User ID.
   *     @type string  $device_id Device identifier.
   *     @type string  $token     FCM token value.
   *     @type bool    $single    Whether to return a single result.
   * }
   * @return FCMToken|FCMToken[]|null The found token(s) or null if not found.
   */
  public function get($args = []): FCMToken|array|null {
    $single = $args['single'] ?? false;
    $where = 'WHERE 1=1';
    $params = [];

    if (isset($args['id'])) {
      $where .= ' AND id = %d';
      $params[] = $args['id'];
    }
    if (isset($args['user_id'])) {
      $where .= ' AND user_id = %d';
      $params[] = $args['user_id'];
    }
    if (isset($args['device_id'])) {
      $where .= ' AND device_id = %s';
      $params[] = $args['device_id'];
    }
    if (isset($args['token'])) {
      $where .= ' AND token = %s';
      $params[] = $args['token'];
    }

    $table_name = self::table_name();
    $query = $this->wpdb->prepare(
      "SELECT * FROM {$table_name} {$where}",
      $params
    );

    $results = $this->wpdb->get_results($query, ARRAY_A);
    if ($this->wpdb->last_error) {
      throw new TokenDatabaseException(
        "Database error fetching tokens: {$this->wpdb->last_error}"
      );
    }

    if (empty($results)) {
      return null;
    }

    if ($single) {
      return new FCMToken($results[0]);
    }

    return array_map(fn($row) => new FCMToken($row), $results);
  }

  /**
   * Adds a new FCM token.
   *
   * @param FCMToken $token Token to add
   * @return FCMToken|null The created token or null on failure
   */
  public function add(FCMToken $token): ?FCMToken {
    $table_name = self::table_name();
    $inserted = $this->wpdb->insert($table_name, [
      'user_id' => $token->user_id,
      'device_id' => $token->device_id,
      'token' => $token->token,
      'device_type' => $token->device_type,
      'device_name' => $token->device_name,
      'app_version' => $token->app_version,
      'created_at' => current_time('mysql'),
      'last_used_at' => current_time('mysql'),
    ]);

    if ($this->wpdb->last_error) {
      throw new TokenDatabaseException(
        "Database error adding token: {$this->wpdb->last_error}"
      );
    }

    if (!$inserted) {
      return null;
    }

    return $this->get(['id' => $this->wpdb->insert_id, 'single' => true]);
  }

  /**
   * Updates device information including token, name and app version.
   *
   * @param int $id Device ID
   * @param array $fields Fields to update
   * @return FCMToken|null Updated token or null on failure
   */
  public function update_token(int $id, array $fields = []): ?FCMToken {
    $table_name = self::table_name();
    $data = [];

    // Only include fields that were actually passed
    if (isset($fields['token'])) {
      $data['token'] = $fields['token'];
    }
    if (isset($fields['device_name'])) {
      $data['device_name'] = $fields['device_name'];
    }
    if (isset($fields['app_version'])) {
      $data['app_version'] = $fields['app_version'];
    }

    // Always update last_used
    $data['last_used_at'] = current_time('mysql');

    $updated = $this->wpdb->update($table_name, $data, ['id' => $id]);
    if ($this->wpdb->last_error) {
      throw new TokenDatabaseException(
        "Database error updating token: {$this->wpdb->last_error}"
      );
    }

    if (!$updated) {
      return null;
    }

    return $this->get(['id' => $id, 'single' => true]);
  }

  public function delete(int $id): bool {
    $table_name = self::table_name();
    $this->wpdb->delete($table_name, ['id' => $id]);

    if ($this->wpdb->last_error) {
      throw new TokenDatabaseException(
        "Database error deleting token: {$this->wpdb->last_error}"
      );
    }

    return true;
  }

  /**
   * Deletes tokens that haven't been used in the specified number of days.
   *
   * @param int $days_threshold Number of days of inactivity before deletion
   * @return int Number of tokens deleted
   * @throws TokenDatabaseException If database error occurs
   */
  public function delete_inactive_tokens(int $days_threshold = 30): int {
    $table_name = self::table_name();
    $sql = $this->wpdb->prepare(
      "DELETE FROM {$table_name} WHERE last_used_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
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
