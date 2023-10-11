<?php

include_once 'factory/unittest-factory.php';
/**
 * Class WPBB_UnitTestCase
 * 
 * This class is used to set up the plugin's custom tables for unit testing
 */
abstract class WPBB_UnitTestCase extends WP_UnitTestCase {

	protected $plugin_path = '/var/www/html/wp-content/plugins/wp-bracket-builder/';

	protected static function factory() {
		static $factory = null;
		if (!$factory) {
			$factory = new WPBB_UnitTest_Factory();
		}
		return $factory;
	}

	public static function set_up_before_class() {
		parent::set_up_before_class();

		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wpbb-activator.php';
		$activator = new Wpbb_Activator();
		$activator->activate();
	}

	public function set_up() {
		wp_set_current_user(1);
	}
}
