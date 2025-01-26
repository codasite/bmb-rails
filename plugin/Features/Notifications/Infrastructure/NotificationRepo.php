<?php

namespace WStrategies\BMB\Features\Notifications\Infrastructure;

use WStrategies\BMB\Features\Notifications\Domain\Notification;
use WStrategies\BMB\Includes\Repository\CustomTableNames;
use WStrategies\BMB\Includes\Repository\RepositoryBase;
use WStrategies\BMB\Includes\Repository\Exceptions\RepositoryDeleteException;

/**
 * Repository for managing notifications in the database.
 *
 * Handles CRUD operations for notifications and maintains the custom database table.
 */
class NotificationRepo extends RepositoryBase {
  /**
   * Create Notification model from database row.
   *
   * @param array $row Database row
   * @return Notification
   */
  protected function create_model(array $row): Notification {
    return new Notification($row);
  }

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
      'title' => [
        'type' => '%s',
        'required' => true,
        'updateable' => true,
      ],
      'message' => [
        'type' => '%s',
        'required' => true,
        'updateable' => true,
      ],
      'timestamp' => [
        'type' => '%s',
        'required' => false,
        'searchable' => true,
        'default' => 'CURRENT_TIMESTAMP',
      ],
      'is_read' => [
        'type' => '%d',
        'required' => false,
        'searchable' => true,
        'updateable' => true,
        'default' => 0,
      ],
      'link' => [
        'type' => '%s',
        'required' => false,
        'updateable' => true,
      ],
      'notification_type' => [
        'type' => '%s',
        'required' => true,
        'searchable' => true,
      ],
    ];
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
    // Add default ordering by timestamp
    $args['orderby'] = $args['orderby'] ?? 'timestamp';
    $args['order'] = $args['order'] ?? 'DESC';

    return parent::get($args);
  }

  /**
   * Adds a new notification.
   *
   * @param Notification $notification Notification to add
   * @return Notification|null The created notification or null on failure
   */
  public function add(Notification $notification): ?Notification {
    return $this->insert([
      'user_id' => $notification->user_id,
      'title' => $notification->title,
      'message' => $notification->message,
      'timestamp' => $notification->timestamp->format('c'),
      'is_read' => $notification->is_read,
      'link' => $notification->link,
      'notification_type' => $notification->notification_type->value,
    ]);
  }

  /**
   * Updates a notification.
   *
   * @param int $id Notification ID
   * @param array $fields Fields to update
   * @return Notification|null Updated notification or null on failure
   */
  public function update(int $id, array $fields = []): ?Notification {
    return parent::update($id, $fields);
  }

  /**
   * Deletes a notification.
   *
   * @param int $id Notification ID to delete
   * @return bool Whether deletion was successful
   */
  public function delete(int $id): bool {
    return parent::delete($id);
  }

  /**
   * Deletes old notifications based on age threshold.
   *
   * @param int $days_threshold Number of days before deletion
   * @return int Number of notifications deleted
   * @throws RepositoryDeleteException If database error occurs
   */
  public function delete_old_notifications(int $days_threshold = 30): int {
    $sql = $this->wpdb->prepare(
      "DELETE FROM {$this->table_name} WHERE timestamp < DATE_SUB(NOW(), INTERVAL %d DAY)",
      $days_threshold
    );

    $deleted = $this->wpdb->query($sql);
    if ($this->wpdb->last_error) {
      throw new RepositoryDeleteException(
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

  /**
   * Create the database table.
   */
  public static function create_table(): void {
    global $wpdb;
    $table_name = self::table_name();
    $users_table = $wpdb->users;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
      id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
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
}
