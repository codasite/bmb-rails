<?php
require_once 'template-factory.php';
require_once 'tournament-factory.php';

/**
 * Class WPBB_UnitTest_Factory_For_Template
 * 
 * This class is used to create template objects for unit testing
 */

class WPBB_UnitTest_Factory extends WP_UnitTest_Factory {

	public $template;

	public $tournament;

	public function __construct() {
		parent::__construct();
		$this->template = new WPBB_UnitTest_Factory_For_Template($this);
		$this->tournament = new WPBB_UnitTest_Factory_For_Tournament($this);
	}
}
