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
  function __construct(array $args = []) {
    parent::__construct($args);
  }

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
   * Get field definitions for the repository.
   */
  public function get_field_definitions(): array {
    global $wpdb;
    return [
      'id' => [
        'type' => '%d',
        'sql_type' => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
        'required' => false,
        'searchable' => true,
        'updateable' => false,
        'primary_key' => true,
      ],
      'user_id' => [
        'type' => '%d',
        'sql_type' => 'bigint UNSIGNED',
        'required' => true,
        'searchable' => true,
        'updateable' => false,
        'nullable' => false,
        'index' => true,
        'foreign_key' => [
          'table' => $wpdb->users,
          'column' => 'ID',
          'on_delete' => 'CASCADE',
        ],
      ],
      'title' => [
        'type' => '%s',
        'sql_type' => 'varchar(255)',
        'required' => true,
        'updateable' => true,
        'nullable' => false,
      ],
      'message' => [
        'type' => '%s',
        'sql_type' => 'text',
        'required' => true,
        'updateable' => true,
        'nullable' => false,
      ],
      'timestamp' => [
        'type' => '%s',
        'sql_type' => 'datetime',
        'required' => false,
        'searchable' => true,
        'nullable' => false,
        'default' => 'CURRENT_TIMESTAMP',
        'index' => true,
      ],
      'is_read' => [
        'type' => '%d',
        'sql_type' => 'tinyint(1)',
        'required' => false,
        'searchable' => true,
        'updateable' => true,
        'nullable' => false,
        'default' => 0,
      ],
      'link' => [
        'type' => '%s',
        'sql_type' => 'varchar(255)',
        'required' => false,
        'updateable' => true,
        'nullable' => true,
      ],
      'notification_type' => [
        'type' => '%s',
        'sql_type' => 'varchar(50)',
        'required' => true,
        'searchable' => true,
        'nullable' => false,
        'index' => true,
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

  protected function get_table_base_name(): string {
    return 'notifications';
  }
}
