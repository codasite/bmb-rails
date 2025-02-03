<?php

namespace WStrategies\BMB\Features\Notifications\Presentation;

use DateTime;
use WP_CLI;
use WP_CLI\Utils;
use WStrategies\BMB\Features\Notifications\Domain\Notification;
use WStrategies\BMB\Features\Notifications\Domain\NotificationType;
use WStrategies\BMB\Features\Notifications\Infrastructure\NotificationRepo;

/**
 * Manages notifications through WP-CLI commands.
 */
class NotificationCommand {
  private NotificationRepo $notification_repo;

  public function __construct() {
    $this->notification_repo = new NotificationRepo();
  }

  /**
   * Lists notifications with optional filtering.
   *
   * ## OPTIONS
   *
   * [--user_id=<user_id>]
   * : Filter notifications by user ID
   *
   * [--type=<notification_type>]
   * : Filter by notification type (bracket_upcoming|bracket_results|round_complete|tournament_start|system)
   *
   * [--read=<is_read>]
   * : Filter by read status (true|false)
   *
   * [--format=<format>]
   * : Render output in a particular format (table|json|csv|yaml|count)
   * ---
   * default: table
   * options:
   *   - table
   *   - json
   *   - csv
   *   - yaml
   *   - count
   * ---
   *
   * ## EXAMPLES
   *
   *     # List all notifications
   *     $ wp wpbb notification list
   *
   *     # List unread notifications for a specific user
   *     $ wp wpbb notification list --user_id=123 --read=false
   *
   *     # List notifications of a specific type
   *     $ wp wpbb notification list --type=bracket_upcoming
   *
   * @param array $args
   * @param array $assoc_args
   */
  public function list($args, $assoc_args) {
    $query = [];

    if (isset($assoc_args['user_id'])) {
      $query['user_id'] = (int) $assoc_args['user_id'];
    }

    if (
      isset($assoc_args['type']) &&
      NotificationType::is_valid($assoc_args['type'])
    ) {
      $query['notification_type'] = $assoc_args['type'];
    }

    if (isset($assoc_args['read'])) {
      $query['is_read'] = filter_var(
        $assoc_args['read'],
        FILTER_VALIDATE_BOOLEAN
      );
    }

    $notifications = $this->notification_repo->get($query);

    if (empty($notifications)) {
      WP_CLI::warning('No notifications found.');
      return;
    }

    $items = array_map(function ($notification) {
      return [
        'id' => $notification->id,
        'user_id' => $notification->user_id,
        'title' => $notification->title,
        'message' => $notification->message,
        'timestamp' => $notification->timestamp->format('Y-m-d H:i:s'),
        'is_read' => $notification->is_read ? 'Yes' : 'No',
        'type' => $notification->notification_type->value,
        'link' => $notification->link ?? '',
      ];
    }, $notifications);

    Utils\format_items($assoc_args['format'], $items, [
      'id',
      'user_id',
      'title',
      'message',
      'timestamp',
      'is_read',
      'type',
      'link',
    ]);
  }

  /**
   * Creates a new notification.
   *
   * ## OPTIONS
   *
   * --user_id=<user_id>
   * : The WordPress user ID to send the notification to
   *
   * --title=<title>
   * : The notification title
   *
   * --message=<message>
   * : The notification message
   *
   * --type=<notification_type>
   * : The type of notification (bracket_upcoming|bracket_results|round_complete|tournament_start|system)
   *
   * [--link=<link>]
   * : Optional link associated with the notification
   *
   * ## EXAMPLES
   *
   *     # Create a system notification
   *     $ wp wpbb notification create --user_id=123 --title="Test" --message="Test message" --type=system
   *
   *     # Create a notification with a link
   *     $ wp wpbb notification create --user_id=123 --title="New bracket" --message="Check out the new bracket" --type=bracket_upcoming --link="https://example.com/bracket/123"
   *
   * @param array $args
   * @param array $assoc_args
   */
  public function create($args, $assoc_args) {
    if (
      !isset(
        $assoc_args['user_id'],
        $assoc_args['title'],
        $assoc_args['message'],
        $assoc_args['type']
      )
    ) {
      WP_CLI::error(
        'Missing required arguments. Please provide --user_id, --title, --message, and --type.'
      );
      return;
    }

    if (!NotificationType::is_valid($assoc_args['type'])) {
      WP_CLI::error(
        'Invalid notification type. Must be one of: bracket_upcoming, bracket_results, round_complete, tournament_start, system'
      );
      return;
    }

    try {
      $notification = new Notification([
        'user_id' => (int) $assoc_args['user_id'],
        'title' => $assoc_args['title'],
        'message' => $assoc_args['message'],
        'notification_type' => $assoc_args['type'],
        'link' => $assoc_args['link'] ?? null,
        'timestamp' => new DateTime(),
        'is_read' => false,
      ]);

      $created = $this->notification_repo->add($notification);

      if ($created) {
        WP_CLI::success(
          sprintf('Created notification with ID: %d', $created->id)
        );
      } else {
        WP_CLI::error('Failed to create notification');
      }
    } catch (\Exception $e) {
      WP_CLI::error($e->getMessage());
    }
  }

  /**
   * Updates an existing notification.
   *
   * ## OPTIONS
   *
   * <id>
   * : The notification ID to update
   *
   * [--title=<title>]
   * : The new notification title
   *
   * [--message=<message>]
   * : The new notification message
   *
   * [--read=<is_read>]
   * : Mark notification as read/unread (true|false)
   *
   * [--link=<link>]
   * : Update the associated link
   *
   * ## EXAMPLES
   *
   *     # Update notification title and message
   *     $ wp wpbb notification update 123 --title="New Title" --message="New message"
   *
   *     # Mark notification as read
   *     $ wp wpbb notification update 123 --read=true
   *
   * @param array $args
   * @param array $assoc_args
   */
  public function update($args, $assoc_args) {
    if (empty($args[0])) {
      WP_CLI::error('Please provide a notification ID.');
      return;
    }

    $id = (int) $args[0];
    $notification = $this->notification_repo->get([
      'id' => $id,
      'single' => true,
    ]);

    if (!$notification) {
      WP_CLI::error(sprintf('Notification with ID %d not found.', $id));
      return;
    }

    $fields = [];

    if (isset($assoc_args['title'])) {
      $fields['title'] = $assoc_args['title'];
    }

    if (isset($assoc_args['message'])) {
      $fields['message'] = $assoc_args['message'];
    }

    if (isset($assoc_args['read'])) {
      $fields['is_read'] = filter_var(
        $assoc_args['read'],
        FILTER_VALIDATE_BOOLEAN
      );
    }

    if (isset($assoc_args['link'])) {
      $fields['link'] = $assoc_args['link'];
    }

    if (empty($fields)) {
      WP_CLI::warning('No fields to update.');
      return;
    }

    try {
      $updated = $this->notification_repo->update($id, $fields);

      if ($updated) {
        WP_CLI::success(sprintf('Updated notification with ID: %d', $id));
      } else {
        WP_CLI::error('Failed to update notification');
      }
    } catch (\Exception $e) {
      WP_CLI::error($e->getMessage());
    }
  }

  /**
   * Deletes a notification.
   *
   * ## OPTIONS
   *
   * <id>
   * : The notification ID to delete
   *
   * ## EXAMPLES
   *
   *     # Delete a notification
   *     $ wp wpbb notification delete 123
   *
   * @param array $args
   * @param array $assoc_args
   */
  public function delete($args, $assoc_args) {
    if (empty($args[0])) {
      WP_CLI::error('Please provide a notification ID.');
      return;
    }

    $id = (int) $args[0];
    $notification = $this->notification_repo->get([
      'id' => $id,
      'single' => true,
    ]);

    if (!$notification) {
      WP_CLI::error(sprintf('Notification with ID %d not found.', $id));
      return;
    }

    try {
      $deleted = $this->notification_repo->delete($id);

      if ($deleted) {
        WP_CLI::success(sprintf('Deleted notification with ID: %d', $id));
      } else {
        WP_CLI::error('Failed to delete notification');
      }
    } catch (\Exception $e) {
      WP_CLI::error($e->getMessage());
    }
  }
}
