<?php

namespace WStrategies\BMB\tests\integration\factory;
use Exception;
use WP_Error;
use WP_UnitTest_Factory_For_Thing;
use WStrategies\BMB\Features\Notifications\Notification;
use WStrategies\BMB\Includes\Factory\NotificationFactory;
use WStrategies\BMB\Includes\Repository\NotificationRepo;

/**
 * Class WPBB_UnitTest_Factory_For_Play
 *
 * This class is used to create template objects for unit testing
 */
class NotificationTestFactory extends WP_UnitTest_Factory_For_Thing {
  private $notification_repo;

  function __construct($factory = null) {
    parent::__construct($factory);
    $this->notification_repo = new NotificationRepo();

    $this->default_generation_definitions = [];
  }

  function create_object($args): WP_Error|int|Notification|null {
    $notification = $this->notification_repo->add(
      NotificationFactory::create($args)
    );
    return $notification;
  }

  function update_object($id, $fields) {
    throw new Exception('Not implemented');
  }

  function get_object_by_id($id) {
    return $this->notification_repo->get(['id' => $id, 'single' => true]);
  }
}
