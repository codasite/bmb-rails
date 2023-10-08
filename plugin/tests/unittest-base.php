<?php

/**
 * Class WPBB_UnitTestCase
 * 
 * This class is used to set up the plugin's custom tables for unit testing
 */
abstract class WPBB_UnitTestCase extends WP_UnitTestCase {

	protected $plugin_path = '/var/www/html/wp-content/plugins/wp-bracket-builder/';

	public static function set_up_before_class() {
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-bracket-builder-activator.php';

		$activator = new Wp_Bracket_Builder_Activator();
		$activator->activate();
	}
}
