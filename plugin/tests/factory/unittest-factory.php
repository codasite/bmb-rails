<?php
require_once 'bracket-factory.php';
require_once 'play-factory.php';
require_once 'team-factory.php';

/**
 * Class WPBB_UnitTest_Factory_For_Template
 *
 * This class is used to create template objects for unit testing
 */

class WPBB_UnitTest_Factory extends WP_UnitTest_Factory {
  public $bracket;
  public $play;
  public $team;

  public function __construct() {
    parent::__construct();
    $this->play = new WPBB_UnitTest_Factory_For_Play($this);
    $this->bracket = new WPBB_UnitTest_Factory_For_Bracket($this);
    $this->team = new WPBB_UnitTest_Factory_For_Team($this);
  }
}
