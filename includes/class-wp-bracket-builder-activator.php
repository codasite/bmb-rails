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

		self::create_sports_table($prefix);
		self::create_teams_table($prefix);
		self::create_tournament_table($prefix);
		self::create_rounds_table($prefix);
		self::create_brackets_table($prefix);
		// self::create_predictions_table($prefix);
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
			sport_id mediumint(9) NOT NULL,
			PRIMARY KEY (id),
			FOREIGN KEY (sport_id) REFERENCES {$prefix}sports(id) ON DELETE CASCADE
		) $charset_collate;";

		// import dbDelta
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	private static function create_tournament_table(string $prefix) {
		/**
		 * Create the tournaments table
		 */

		global $wpdb;
		$table_name = $prefix . 'tournaments';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			sport_id mediumint(9) NOT NULL,
			wildcard_teams mediumint(9) NOT NULL,
			PRIMARY KEY (id),
			FOREIGN KEY (sport_id) REFERENCES {$prefix}sports(id) ON DELETE CASCADE
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
			tournament_id mediumint(9) NOT NULL,
			PRIMARY KEY (id),
			FOREIGN KEY (tournament_id) REFERENCES {$prefix}tournaments(id)
		) $charset_collate;";

		// import dbDelta
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	private static function create_brackets_table(string $prefix) {
		/**
		 * Create the brackets table
		 */

		global $wpdb;
		$table_name = $prefix . 'brackets';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			cust_id mediumint(9),
			tournament_id mediumint(9) NOT NULL,
			PRIMARY KEY (id),
			FOREIGN KEY (tournament_id) REFERENCES {$prefix}tournaments(id)
		) $charset_collate;";

		// import dbDelta
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	private static function create_preditions_table(string $prefix) {
		/**
		 * Create the predictions table
		 */

		global $wpdb;
		$table_name = $prefix . 'predictions';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			team_id mediumint(9) NOT NULL,
			bracket_id mediumint(9) NOT NULL,
			round_id mediumint(9) NOT NULL,
			lft int(11) NOT NULL,
			rgt int(11) NOT NULL,
			in_order int(11) NOT NULL,
			PRIMARY KEY (id),
			FOREIGN KEY (team_id) REFERENCES {$prefix}teams(id),
			FOREIGN KEY (bracket_id) REFERENCES {$prefix}brackets(id),
			FOREIGN KEY (round_id) REFERENCES {$prefix}rounds(id)
		) $charset_collate;";

		// import dbDelta
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}

