<?php

namespace WStrategies\BMB\Features\Notifications\Presentation;

use WStrategies\BMB\Includes\Controllers\Traits\RestDeleteItemTrait;
use WStrategies\BMB\Includes\Controllers\Traits\RestGetCollectionTrait;
use WP_Error;
use WStrategies\BMB\Features\Notifications\Presentation\NotificationSerializer;
use WStrategies\BMB\Includes\Service\Serializer\ApiSerializerInterface;
use WStrategies\BMB\Features\Notifications\Infrastructure\NotificationRepo;
use WStrategies\BMB\Includes\Controllers\RestApiBase;

class NotificationApi extends RestApiBase {
  use RestGetCollectionTrait;
  use RestDeleteItemTrait;

  protected $rest_base = 'notifications';

  public function __construct() {
    $this->notification_repo = new NotificationRepo();
    parent::__construct([
      'rest_base' => $this->rest_base,
      'serializer' => new NotificationSerializer(),
      'repository' => new NotificationRepo(),
    ]);
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
   * Check if a notification can be deleted.
   */
  protected function can_delete_item(int $id, bool $force): bool|WP_Error {
    // First verify the notification belongs to the current user
    $items = $this->repository->get([
      'id' => $id,
      'user_id' => get_current_user_id(),
      'single' => true,
    ]);

    if (empty($items)) {
      return new WP_Error(
        'rest_notification_not_found',
        __(
          'Notification not found or you do not have permission to delete it.'
        ),
        ['status' => 404]
      );
    }

    return true;
  }
}
