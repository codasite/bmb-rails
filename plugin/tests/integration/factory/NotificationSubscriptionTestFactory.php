<?php

namespace WStrategies\BMB\tests\integration\factory;
use Exception;
use WP_Error;
use WP_UnitTest_Factory_For_Thing;
use WStrategies\BMB\Features\Notifications\Domain\NotificationSubscription;
use WStrategies\BMB\Features\Notifications\Infrastructure\NotificationSubscriptionRepo;
use WStrategies\BMB\Includes\Factory\NotificationSubscriptionFactory;

/**
 * Class WPBB_UnitTest_Factory_For_Play
 *
 * This class is used to create template objects for unit testing
 */
class NotificationSubscriptionTestFactory extends
  WP_UnitTest_Factory_For_Thing {
  private $notification_sub_repo;

  function __construct($factory = null) {
    parent::__construct($factory);
    $this->notification_sub_repo = new NotificationSubscriptionRepo();

    $this->default_generation_definitions = [];
  }

  function create_object($args): WP_Error|int|NotificationSubscription|null {
    $notification = $this->notification_sub_repo->add(
      NotificationSubscriptionFactory::create($args)
    );
    return $notification;
  }

  function update_object($id, $fields) {
    throw new Exception('Not implemented');
  }

  function get_object_by_id($id) {
    return $this->notification_sub_repo->get(['id' => $id, 'single' => true]);
  }
}
