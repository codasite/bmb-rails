<?php

/**
 * Fired during plugin activation
 *
 * @link       https://https://github.com/barrymolina
 * @since      1.0.0
 *
 * @package    Wp_Bracket_Builder
 * @subpackage Wp_Bracket_Builder/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wp_Bracket_Builder
 * @subpackage Wp_Bracket_Builder/includes
 * @author     Barry Molina <barry@wstrategies.co>
 */
class Wp_Bracket_Builder_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;
		$wp_prefix = $wpdb->prefix;
		$plugin_prefix = 'wpbb_';
		$prefix = $wp_prefix . $plugin_prefix;

		self::create_sports_table($prefix);
		self::create_teams_table($prefix);
	}

	private static function create_sports_table(string $prefix) {
		/**
		 * Create the sports table
		 */

		global $wpdb;
		$table_name = $prefix . 'sports';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			elimination_type tinyint(1) NOT NULL,
			number_of_teams mediumint(9) NOT NULL,
			PRIMARY KEY (id)
		) $charset_collate;";

		// import dbDelta
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	private static function create_teams_table(string $prefix) {
		/**
		 * Create the teams table
		 */

		global $wpdb;
		$table_name = $prefix . 'teams';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			sport_id mediumint(9) NOT NULL,
			PRIMARY KEY (id),
			FOREIGN KEY (sport_id) REFERENCES {$prefix}sports(id)
		) $charset_collate;";

		// import dbDelta
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

}
