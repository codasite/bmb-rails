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

		self::delete_tables($prefix);

		self::create_brackets_table($prefix);
		self::create_teams_table($prefix);
		self::create_rounds_table($prefix);
		self::create_matches_table($prefix);
		self::create_bracket_picks_table($prefix);
		self::create_match_picks_table($prefix);
	}

	private static function delete_tables(string $prefix) {
		global $wpdb;
		$tables = [
			$prefix . 'match_picks',
			$prefix . 'seeds',
			$prefix . 'match_results',
			$prefix . 'bracket_picks',
			$prefix . 'matches',
			$prefix . 'teams',
			$prefix . 'rounds',
			$prefix . 'brackets',
		];

		foreach ($tables as $table) {
			$wpdb->query("DROP TABLE IF EXISTS $table");
		}
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
			cpt_id bigint(20) UNSIGNED NOT NULL,
			-- name varchar(255) NOT NULL,
			-- active tinyint(1) NOT NULL DEFAULT 0,
			num_rounds tinyint(4) NOT NULL,
			num_wildcards tinyint(4) NOT NULL,
			wildcard_placement tinyint(2),
			-- created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY (cpt_id),
			FOREIGN KEY (cpt_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE

		) $charset_collate;";

		// import dbDelta
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	private static function create_bracket_picks_table(string $prefix) {
		/**
		 * Create the brackets table
		 */

		global $wpdb;
		$table_name = $prefix . 'bracket_picks';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			img_url varchar(255),
			customer_id mediumint(9),
			bracket_id mediumint(9) NOT NULL,
			PRIMARY KEY (id),
			FOREIGN KEY (bracket_id) REFERENCES {$prefix}brackets(id) ON DELETE CASCADE
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
			depth tinyint(4) NOT NULL,
			PRIMARY KEY (id),
			FOREIGN KEY (bracket_id) REFERENCES {$prefix}brackets(id) ON DELETE CASCADE
		) $charset_collate;";

		// import dbDelta
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	private static function create_matches_table(string $prefix) {
		/**
		 * Create the nodes table
		 */

		global $wpdb;
		$table_name = $prefix . 'matches';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			round_id mediumint(9) NOT NULL,
			round_index tinyint(4) NOT NULL,
			team1_id mediumint(9),
			team2_id mediumint(9),
			PRIMARY KEY (id),
			FOREIGN KEY (round_id) REFERENCES {$prefix}rounds(id) ON DELETE CASCADE,
			FOREIGN KEY (team1_id) REFERENCES {$prefix}teams(id) ON DELETE CASCADE,
			FOREIGN KEY (team2_id) REFERENCES {$prefix}teams(id) ON DELETE CASCADE
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
			bracket_id mediumint(9) NOT NULL,
			seed tinyint(4),
			PRIMARY KEY (id),
			FOREIGN KEY (bracket_id) REFERENCES {$prefix}brackets(id) ON DELETE CASCADE
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
			match_id mediumint(9) NOT NULL,
			PRIMARY KEY (id),
			FOREIGN KEY (bracket_id) REFERENCES {$prefix}brackets(id) ON DELETE CASCADE,
			FOREIGN KEY (team_id) REFERENCES {$prefix}teams(id) ON DELETE CASCADE,
			FOREIGN KEY (match_id) REFERENCES {$prefix}matches(id) ON DELETE CASCADE
		) $charset_collate;";

		// import dbDelta
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	private static function create_match_results_table(string $prefix) {
		/**
		 * Create the match results table
		 */

		global $wpdb;
		$table_name = $prefix . 'match_results';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			match_id mediumint(9) NOT NULL,
			team_id mediumint(9) NOT NULL,
			PRIMARY KEY (id),
			FOREIGN KEY (match_id) REFERENCES {$prefix}matches(id) ON DELETE CASCADE,
			FOREIGN KEY (team_id) REFERENCES {$prefix}teams(id) ON DELETE CASCADE
		) $charset_collate;";

		// import dbDelta
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}


	private static function create_match_picks_table(string $prefix) {
		/**
		 * Create the predictions table
		 */

		global $wpdb;
		$table_name = $prefix . 'match_picks';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			bracket_pick_id mediumint(9) NOT NULL,
			match_id mediumint(9) NOT NULL,
			team_id mediumint(9) NOT NULL,
			PRIMARY KEY (id),
			FOREIGN KEY (bracket_pick_id) REFERENCES {$prefix}bracket_picks(id) ON DELETE CASCADE,
			FOREIGN KEY (match_id) REFERENCES {$prefix}matches(id) ON DELETE CASCADE,
			FOREIGN KEY (team_id) REFERENCES {$prefix}teams(id) ON DELETE CASCADE
		) $charset_collate;";

		// import dbDelta
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}
