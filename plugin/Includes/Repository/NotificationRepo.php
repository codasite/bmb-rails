<?php

namespace WStrategies\BMB\Includes\Repository;

use WStrategies\BMB\Includes\Domain\Notification;
use WStrategies\BMB\Includes\Domain\NotificationType;
use WStrategies\BMB\Includes\Domain\ValidationException;
use WStrategies\BMB\Includes\Factory\NotificationFactory;

class NotificationRepo {
  private \wpdb $wpdb;

  function __construct() {
    global $wpdb;
    $this->wpdb = $wpdb;
  }

  public function get($args = []) {
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
    if (isset($args['post_id'])) {
      $where .= ' AND post_id = %d';
      $params[] = $args['post_id'];
    }
    if (isset($args['notification_type'])) {
      $where .= ' AND notification_type = %s';
      if ($args['notification_type'] instanceof NotificationType) {
        $params[] = $args['notification_type']->value;
      } else {
        $params[] = $args['notification_type'];
      }
    }
    $table_name = $this->notification_table();
    $query = $this->wpdb->prepare(
      "SELECT * FROM {$table_name} {$where}",
      $params
    );
    $notification_results = $this->wpdb->get_results($query, ARRAY_A);
    $notifications = [];
    foreach ($notification_results as $result) {
      try {
        $notification = NotificationFactory::create($result);
        $notifications[] = $notification;
      } catch (ValidationException $e) {
        // do nothing
      }
    }
    return $single ? array_shift($notifications) : $notifications;
  }

  public function get_by_post_id(
    int $post_id,
    NotificationType $notification_type
  ): array {
    return $this->get([
      'post_id' => $post_id,
      'notification_type' => $notification_type->value,
    ]);
  }

  public function current_user_notification_id(
    int $post_id,
    NotificationType $notification_type
  ): null|int {
    $user = wp_get_current_user();
    if (!$user) {
      return false;
    }
    $notification = $this->get([
      'user_id' => $user->ID,
      'post_id' => $post_id,
      'notification_type' => $notification_type->value,
      'single' => true,
    ]);
    return $notification->id ?? null;
  }

  public function add(Notification $notification): ?Notification {
    // first, check to see if the notification already exists
    $existing = $this->get([
      'user_id' => $notification->user_id,
      'post_id' => $notification->post_id,
      'notification_type' => $notification->notification_type->value,
      'single' => true,
    ]);
    if ($existing) {
      return $existing;
    }
    $table_name = $this->notification_table();
    $this->wpdb->insert($table_name, [
      'post_id' => $notification->post_id,
      'user_id' => $notification->user_id,
      'notification_type' => $notification->notification_type->value,
    ]);
    $id = $this->wpdb->insert_id;

    return $this->get(['id' => $id, 'single' => true]);
  }

  public function delete($id): bool {
    $table_name = $this->notification_table();
    $this->wpdb->delete($table_name, ['id' => $id]);
    if ($this->wpdb->last_error) {
      return false;
    }
    return true;
  }

  public function notification_table(): string {
    return $this->wpdb->prefix . 'bracket_builder_notifications';
  }

  public function create_notification_table(): void {
    $table_name = $this->notification_table();
    $charset_collate = $this->wpdb->get_charset_collate();
    $prefix = $this->wpdb->prefix;
    $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
      id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id bigint UNSIGNED NOT NULL,
      user_id bigint UNSIGNED NOT NULL,
      notification_type varchar(50) NOT NULL,
			PRIMARY KEY (id),
			FOREIGN KEY (post_id) REFERENCES {$this->wpdb->prefix}posts(ID) ON DELETE CASCADE,
			FOREIGN KEY (user_id) REFERENCES {$this->wpdb->prefix}users(ID) ON DELETE CASCADE,
      UNIQUE (post_id, user_id, notification_type)
    ) $charset_collate;";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
  }
}
