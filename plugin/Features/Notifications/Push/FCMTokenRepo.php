<?php

namespace WStrategies\BMB\Features\Notifications\Push;

use WStrategies\BMB\Includes\Repository\CustomTableInterface;
use WStrategies\BMB\Includes\Repository\CustomTableNames;

class FCMTokenRepo implements CustomTableInterface {
  public \wpdb $wpdb;

  function __construct() {
    global $wpdb;
    $this->wpdb = $wpdb;
  }

  public function get($args = []): array|object|null {
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
    return $single ? array_shift($results) : $results;
  }

  public function add(
    int $user_id,
    string $device_id,
    string $token,
    string $device_type,
    ?string $device_name = null,
    ?string $app_version = null
  ): ?array {
    // Check if device already exists for user
    $existing = $this->get([
      'user_id' => $user_id,
      'device_id' => $device_id,
      'single' => true,
    ]);

    if ($existing) {
      // Update existing device
      return $this->update_token($existing['id'], $token);
    }

    $table_name = self::table_name();
    $this->wpdb->insert($table_name, [
      'user_id' => $user_id,
      'device_id' => $device_id,
      'token' => $token,
      'device_type' => $device_type,
      'device_name' => $device_name,
      'app_version' => $app_version,
      'created_at' => current_time('mysql'),
      'last_used_at' => current_time('mysql'),
    ]);

    $id = $this->wpdb->insert_id;
    return $this->get(['id' => $id, 'single' => true]);
  }

  public function update_token(int $id, string $token): ?array {
    $table_name = self::table_name();
    $this->wpdb->update(
      $table_name,
      [
        'token' => $token,
        'last_used_at' => current_time('mysql'),
      ],
      ['id' => $id]
    );
    return $this->get(['id' => $id, 'single' => true]);
  }

  public function delete($id): bool {
    $table_name = self::table_name();
    $this->wpdb->delete($table_name, ['id' => $id]);
    return empty($this->wpdb->last_error);
  }

  public function delete_by_device(int $user_id, string $device_id): bool {
    $table_name = self::table_name();
    $this->wpdb->delete($table_name, [
      'user_id' => $user_id,
      'device_id' => $device_id,
    ]);
    return empty($this->wpdb->last_error);
  }

  public function update_last_used(string $token): bool {
    $table_name = self::table_name();
    return $this->wpdb->update(
      $table_name,
      ['last_used_at' => current_time('mysql')],
      ['token' => $token]
    ) !== false;
  }

  public function get_user_devices(int $user_id): array {
    return $this->get(['user_id' => $user_id]) ?? [];
  }

  public function delete_inactive_tokens(int $days_threshold = 30): int {
    $table_name = self::table_name();
    $sql = $this->wpdb->prepare(
      "DELETE FROM {$table_name} WHERE last_used_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
      $days_threshold
    );
    return $this->wpdb->query($sql);
  }

  public function update_app_version(
    int $user_id,
    string $device_id,
    string $app_version
  ): bool {
    $table_name = self::table_name();
    $rows_affected = $this->wpdb->update(
      $table_name,
      [
        'app_version' => $app_version,
        'last_used_at' => current_time('mysql'),
      ],
      [
        'user_id' => $user_id,
        'device_id' => $device_id,
      ]
    );

    // Return true only if rows were actually updated
    return $rows_affected > 0;
  }

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
