<?php

namespace WStrategies\BMB\Features\Notifications\Infrastructure;

use WStrategies\BMB\Features\Notifications\Domain\Notification;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Features\Notifications\Infrastructure\Exceptions\NotificationCreateException;
use WStrategies\BMB\Features\Notifications\Infrastructure\Exceptions\NotificationUpdateException;
use WStrategies\BMB\Features\Notifications\Infrastructure\Exceptions\NotificationDeleteException;
use WStrategies\BMB\Features\Notifications\Infrastructure\Exceptions\NotificationFetchException;
use WStrategies\BMB\Includes\Repository\CustomTableInterface;
use WStrategies\BMB\Includes\Repository\CustomTableNames;

/**
 * Repository for managing notifications in the database.
 *
 * Handles CRUD operations for notifications and maintains the custom database table.
 */
class NotificationRepo implements CustomTableInterface {
  private \wpdb $wpdb;
  private string $table_name;

  /**
   * Initializes the repository.
   */
  function __construct() {
    global $wpdb;
    $this->wpdb = $wpdb;
    $this->table_name = self::table_name();
  }

  /**
   * Retrieves notifications based on search criteria.
   *
   * @param array $args {
   *     Optional. Arguments for filtering notifications.
   *     @type string  $id               Notification ID.
   *     @type int     $user_id          User ID.
   *     @type bool    $is_read          Read status.
   *     @type string  $notification_type Notification type.
   *     @type bool    $single           Whether to return a single result.
   * }
   * @return Notification|Notification[]|null The found notification(s) or null if not found.
   */
  public function get($args = []): Notification|array|null {
    $single = $args['single'] ?? false;
    $where = 'WHERE 1=1';
    $params = [];

    if (isset($args['id'])) {
      $where .= ' AND id = %s';
      $params[] = $args['id'];
    }
    if (isset($args['user_id'])) {
      $where .= ' AND user_id = %d';
      $params[] = $args['user_id'];
    }
    if (isset($args['is_read'])) {
      $where .= ' AND is_read = %d';
      $params[] = (int) $args['is_read'];
    }
    if (isset($args['notification_type'])) {
      $where .= ' AND notification_type = %s';
      $params[] = $args['notification_type'];
    }

    $query = $this->wpdb->prepare(
      "SELECT * FROM {$this->table_name} {$where} ORDER BY timestamp DESC",
      $params
    );

    $results = $this->wpdb->get_results($query, ARRAY_A);
    if ($this->wpdb->last_error) {
      throw new NotificationFetchException(
        "Database error fetching notifications: {$this->wpdb->last_error}"
      );
    }

    if (empty($results)) {
      return null;
    }

    if ($single) {
      return new Notification($results[0]);
    }

    return array_map(fn($row) => new Notification($row), $results);
  }

  /**
   * Adds a new notification.
   *
   * @param Notification $notification Notification to add
   * @return Notification|null The created notification or null on failure
   */
  public function add(Notification $notification): ?Notification {
    $inserted = $this->wpdb->insert($this->table_name, [
      'user_id' => $notification->user_id,
      'title' => $notification->title,
      'message' => $notification->message,
      'timestamp' => $notification->timestamp->format('c'),
      'is_read' => $notification->is_read,
      'link' => $notification->link,
      'notification_type' => $notification->notification_type->value,
    ]);

    if ($this->wpdb->last_error) {
      throw new NotificationCreateException(
        "Database error adding notification: {$this->wpdb->last_error}"
      );
    }

    if (!$inserted) {
      return null;
    }

    return $this->get(['id' => $this->wpdb->insert_id, 'single' => true]);
  }

  /**
   * Updates a notification.
   *
   * @param string $id Notification ID
   * @param array $fields Fields to update
   * @return Notification|null Updated notification or null on failure
   */
  public function update(string $id, array $fields = []): ?Notification {
    $data = array_intersect_key($fields, [
      'is_read' => true,
      'title' => true,
      'message' => true,
      'link' => true,
    ]);

    if (empty($data)) {
      return $this->get(['id' => $id, 'single' => true]);
    }

    $updated = $this->wpdb->update($this->table_name, $data, ['id' => $id]);
    if ($this->wpdb->last_error) {
      throw new NotificationUpdateException(
        "Database error updating notification: {$this->wpdb->last_error}"
      );
    }

    if ($updated === false) {
      return null;
    }

    return $this->get(['id' => $id, 'single' => true]);
  }

  /**
   * Deletes a notification.
   *
   * @param string $id Notification ID to delete
   * @return bool Whether deletion was successful
   */
  public function delete(string $id): bool {
    $deleted = $this->wpdb->delete($this->table_name, ['id' => $id]);

    if ($this->wpdb->last_error) {
      throw new NotificationDeleteException(
        "Database error deleting notification: {$this->wpdb->last_error}"
      );
    }

    return $deleted !== false;
  }

  /**
   * Deletes old notifications based on age threshold.
   *
   * @param int $days_threshold Number of days before deletion
   * @return int Number of notifications deleted
   */
  public function delete_old_notifications(int $days_threshold = 30): int {
    $sql = $this->wpdb->prepare(
      "DELETE FROM {$this->table_name} WHERE timestamp < DATE_SUB(NOW(), INTERVAL %d DAY)",
      $days_threshold
    );

    $deleted = $this->wpdb->query($sql);
    if ($this->wpdb->last_error) {
      throw new NotificationDeleteException(
        "Database error deleting old notifications: {$this->wpdb->last_error}"
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
    return CustomTableNames::table_name('notifications');
  }

  public static function create_table(): void {
    global $wpdb;
    $table_name = self::table_name();
    $users_table = $wpdb->users;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
      id char(36) NOT NULL,
      user_id bigint UNSIGNED NOT NULL,
      title varchar(255) NOT NULL,
      message text NOT NULL,
      timestamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      is_read tinyint(1) NOT NULL DEFAULT 0,
      link varchar(255),
      notification_type varchar(50) NOT NULL,
      PRIMARY KEY (id),
      KEY user_id (user_id),
      KEY timestamp (timestamp),
      KEY notification_type (notification_type),
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
