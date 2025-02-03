<?php
namespace WStrategies\BMB\tests\integration\factory;

use WP_UnitTest_Factory;

/**
 * Class WPBB_UnitTest_Factory_For_Template
 *
 * This class is used to create template objects for unit testing
 */

class TestFactory extends WP_UnitTest_Factory {
  public $bracket;
  public $play;
  public $team;
  public $notification;

  public function __construct() {
    parent::__construct();
    $this->play = new PlayTestFactory($this);
    $this->bracket = new BracketTestFactory($this);
    $this->team = new TeamTestFactory($this);
    $this->notification = new NotificationSubscriptionTestFactory($this);
  }
}
