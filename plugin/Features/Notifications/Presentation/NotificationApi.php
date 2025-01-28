<?php

namespace BMB\Features\Notifications\Presentation;

use BMB\Features\Notifications\Presentation\Traits\RestGetCollectionTrait;
use WP_Error;
use WStrategies\BMB\Features\Notifications\Presentation\NotificationSerializer;
use WStrategies\BMB\Includes\Service\Serializer\ApiSerializerInterface;
use WStrategies\BMB\Features\Notifications\Infrastructure\NotificationRepo;
class NotificationApi extends RestApiBase {
  use RestGetCollectionTrait;

  protected $rest_base = 'notifications';
  protected NotificationRepo $notification_repo;

  public function __construct() {
    $this->notification_repo = new NotificationRepo();
    parent::__construct($this->rest_base, new NotificationSerializer());
  }

  /**
   * Get notifications for the collection.
   * Implementation of abstract method from RestGetCollectionTrait.
   */
  protected function get_collection_items(
    int $page,
    int $per_page,
    string $search
  ): array|WP_Error {
    $offset = ($page - 1) * $per_page;

    return $this->notification_repo->get(['user_id' => get_current_user_id()]);
  }
}
