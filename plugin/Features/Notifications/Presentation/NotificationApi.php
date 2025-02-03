<?php

namespace WStrategies\BMB\Features\Notifications\Presentation;

use WStrategies\BMB\Includes\Controllers\Traits\RestDeleteItemTrait;
use WStrategies\BMB\Includes\Controllers\Traits\RestGetCollectionTrait;
use WP_Error;
use WStrategies\BMB\Features\Notifications\Application\NotificationManager;
use WStrategies\BMB\Features\Notifications\Presentation\NotificationSerializer;
use WStrategies\BMB\Includes\Service\Serializer\ApiSerializerInterface;
use WStrategies\BMB\Features\Notifications\Infrastructure\NotificationRepo;
use WStrategies\BMB\Includes\Controllers\RestApiBase;

class NotificationApi extends RestApiBase {
  use RestGetCollectionTrait;
  use RestDeleteItemTrait;

  protected $rest_base = 'notifications';
  private NotificationManager $notification_manager;

  public function __construct() {
    $notification_repo = new NotificationRepo();
    $this->notification_manager = new NotificationManager([
      'notification_repo' => $notification_repo,
    ]);
    parent::__construct([
      'rest_base' => $this->rest_base,
      'serializer' => new NotificationSerializer(),
      'repository' => $notification_repo,
    ]);
  }

  public function register_routes(): void {
    parent::register_routes();

    // Register mark as read endpoint
    register_rest_route(
      $this->namespace,
      '/' . $this->rest_base . '/(?P<id>[\d]+)/read',
      [
        [
          'methods' => \WP_REST_Server::EDITABLE,
          'callback' => [$this, 'mark_as_read'],
          'permission_callback' => [$this, 'mark_as_read_permissions_check'],
          'args' => [],
        ],
      ]
    );

    // Register mark all as read endpoint
    register_rest_route(
      $this->namespace,
      '/' . $this->rest_base . '/read-all',
      [
        [
          'methods' => \WP_REST_Server::EDITABLE,
          'callback' => [$this, 'mark_all_as_read'],
          'permission_callback' => [
            $this,
            'mark_all_as_read_permissions_check',
          ],
          'args' => [],
        ],
      ]
    );
  }

  /**
   * Mark a notification as read.
   */
  public function mark_as_read($request): \WP_REST_Response|WP_Error {
    $notification_id = (int) $request['id'];
    $notification = $this->notification_manager->mark_as_read($notification_id);

    if (!$notification) {
      return new WP_Error(
        'rest_notification_not_found',
        __('Notification not found.'),
        ['status' => 404]
      );
    }

    return rest_ensure_response(
      $this->prepare_item_for_response($notification, $request)
    );
  }

  /**
   * Check if current user can mark notification as read.
   */
  public function mark_as_read_permissions_check($request): bool|WP_Error {
    return $this->item_belongs_to_user((int) $request['id']);
  }

  /**
   * Mark all notifications as read for the current user.
   */
  public function mark_all_as_read($request): \WP_REST_Response {
    $count = $this->notification_manager->mark_all_as_read(
      get_current_user_id()
    );

    return new \WP_REST_Response(['marked_as_read' => $count], 200);
  }

  /**
   * Check if current user can mark all notifications as read.
   */
  public function mark_all_as_read_permissions_check($request): bool {
    return is_user_logged_in();
  }

  /**
   * Get filters for collection query.
   */
  protected function get_collection_filters(
    int $page,
    int $per_page,
    string $search
  ): array {
    return [
      'user_id' => get_current_user_id(),
      'orderby' => 'timestamp',
      'order' => 'DESC',
    ];
  }

  /**
   * Get filters for single item query.
   */
  protected function get_single_item_filters(int $id): array {
    return array_merge(parent::get_single_item_filters($id), [
      'user_id' => get_current_user_id(),
    ]);
  }

  /**
   * Check if an item exists and belongs to the current user.
   *
   * @param int $item_id The ID of the item to check
   * @return bool|WP_Error True if the item exists and belongs to the user, WP_Error otherwise
   */
  protected function item_belongs_to_user(int $item_id): bool|WP_Error {
    $item = $this->repository->get([
      'id' => $item_id,
      'user_id' => get_current_user_id(),
      'single' => true,
    ]);

    if (empty($item)) {
      return new WP_Error(
        'rest_notification_not_found',
        __('Notification not found.'),
        ['status' => 404]
      );
    }

    return true;
  }

  /**
   * Check if the current user can delete notifications.
   */
  public function delete_item_permissions_check($request): bool|WP_Error {
    return $this->item_belongs_to_user((int) $request['id']);
  }
}
