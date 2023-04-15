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
		$plugin_prefix = 'bracket_builder_';
		$prefix = $wp_prefix . $plugin_prefix;

		// self::create_sports_table($prefix);
		self::create_teams_table($prefix);
		self::create_brackets_table($prefix);
		self::create_rounds_table($prefix);
		self::create_nodes_table($prefix);
		self::create_seeds_table($prefix);
		self::create_user_brackets_table($prefix);
		self::create_predictions_table($prefix);
	}

	private static function create_sports_table(string $prefix) {
		/**
		 * Create the sports table
		 */

		global $wpdb;
		$table_name = $prefix . 'sports';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
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

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			PRIMARY KEY (id)
		) $charset_collate;";

		// import dbDelta
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	private static function create_brackets_table(string $prefix) {
		/**
		 * Create the tournaments table
		 */

		global $wpdb;
		$table_name = $prefix . 'brackets';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			PRIMARY KEY (id)
		) $charset_collate;";

		// import dbDelta
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	private static function create_rounds_table(string $prefix) {
		/**
		 * Create the rounds table
		 */

		global $wpdb;
		$table_name = $prefix . 'rounds';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			bracket_id mediumint(9) NOT NULL,
			PRIMARY KEY (id),
			FOREIGN KEY (bracket_id) REFERENCES {$prefix}brackets(id)
		) $charset_collate;";

		// import dbDelta
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	private static function create_nodes_table(string $prefix) {
		/**
		 * Create the nodes table
		 */

		global $wpdb;
		$table_name = $prefix . 'nodes';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			round_id mediumint(9) NOT NULL,
			lft int(11) NOT NULL,
			rgt int(11) NOT NULL,
			in_order int(11) NOT NULL,
			PRIMARY KEY (id),
			FOREIGN KEY (round_id) REFERENCES {$prefix}rounds(id)
		) $charset_collate;";

		// import dbDelta
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	private static function create_seeds_table(string $prefix) {
		/**
		 * Create the seeds table
		 */

		global $wpdb;
		$table_name = $prefix . 'seeds';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			bracket_id mediumint(9) NOT NULL,
			team_id mediumint(9) NOT NULL,
			node_id mediumint(9) NOT NULL,
			PRIMARY KEY (id),
			FOREIGN KEY (bracket_id) REFERENCES {$prefix}brackets(id),
			FOREIGN KEY (team_id) REFERENCES {$prefix}teams(id),
			FOREIGN KEY (node_id) REFERENCES {$prefix}nodes(id)
		) $charset_collate;";

		// import dbDelta
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	private static function create_user_brackets_table(string $prefix) {
		/**
		 * Create the brackets table
		 */

		global $wpdb;
		$table_name = $prefix . 'user_brackets';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			cust_id mediumint(9),
			bracket_id mediumint(9) NOT NULL,
			PRIMARY KEY (id),
			FOREIGN KEY (bracket_id) REFERENCES {$prefix}brackets(id)
		) $charset_collate;";

		// import dbDelta
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	private static function create_predictions_table(string $prefix) {
		/**
		 * Create the predictions table
		 */

		global $wpdb;
		$table_name = $prefix . 'predictions';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			user_bracket_id mediumint(9) NOT NULL,
			node_id mediumint(9) NOT NULL,
			team_id mediumint(9) NOT NULL,
			PRIMARY KEY (id),
			FOREIGN KEY (user_bracket_id) REFERENCES {$prefix}user_brackets(id),
			FOREIGN KEY (node_id) REFERENCES {$prefix}nodes(id),
			FOREIGN KEY (team_id) REFERENCES {$prefix}teams(id)
		) $charset_collate;";

		// import dbDelta
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}
