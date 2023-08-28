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

		// self::create_rounds_table($prefix);
		self::create_teams_table($prefix);
		self::create_matches_table($prefix);
		self::create_match_picks_table($prefix);
		self::create_max_team_table($prefix);
	}

	private static function delete_tables(string $prefix) {
		global $wpdb;
		$tables = [
			$prefix . 'match_picks',
			$prefix . 'matches',
			$prefix . 'teams',
			$prefix . 'rounds',
			$prefix . 'max_teams'
		];

		foreach ($tables as $table) {
			$wpdb->query("DROP TABLE IF EXISTS $table");
		}
	}

	// private static function create_rounds_table(string $prefix) {
	// 	/**
	// 	 * Create the rounds table
	// 	 */

	// 	global $wpdb;
	// 	$table_name = $prefix . 'rounds';
	// 	$charset_collate = $wpdb->get_charset_collate();

	// 	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
	// 		id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	// 		name varchar(255) NOT NULL,
	// 		bracket_template_id bigint(20) UNSIGNED NOT NULL,
	// 		depth tinyint(4) NOT NULL,
	// 		round_index tinyint(4) NOT NULL,
	// 		PRIMARY KEY (id),
	// 		UNIQUE KEY (bracket_template_id),
	// 		FOREIGN KEY (bracket_template_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE
	// 	) $charset_collate;";

	// 	// import dbDelta
	// 	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	// 	dbDelta($sql);
	// }

	private static function create_matches_table(string $prefix) {
		/**
		 * Create the matches table
		 */

		global $wpdb;
		$table_name = $prefix . 'matches';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	 		bracket_template_id bigint(20) UNSIGNED NOT NULL,
			round_index tinyint(4) NOT NULL,
			match_index tinyint(4) NOT NULL,
			team1_id bigint(20) UNSIGNED,
			team2_id bigint(20) UNSIGNED,
			PRIMARY KEY (id),
	 		FOREIGN KEY (bracket_template_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE,
			FOREIGN KEY (team1_id) REFERENCES {$prefix}teams(id) ON DELETE SET NULL,
			FOREIGN KEY (team2_id) REFERENCES {$prefix}teams(id) ON DELETE SET NULL
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
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			bracket_template_id bigint(20) UNSIGNED NOT NULL,
			PRIMARY KEY (id),
			FOREIGN KEY (bracket_template_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE
		) $charset_collate;";

		// import dbDelta
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	private static function create_match_picks_table(string $prefix) {
		/**
		 * Create the match picks table. Rows in this table represent a user's pick for a match.
		 * Holds a pointer to the bracket play this pick belongs to.
		 */

		global $wpdb;
		$table_name = $prefix . 'match_picks';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			bracket_play_id bigint(20) UNSIGNED NOT NULL,
			match_id bigint(20) UNSIGNED NOT NULL,
			team_id bigint(20) UNSIGNED NOT NULL,
			PRIMARY KEY (id),
			FOREIGN KEY (bracket_play_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE,
			FOREIGN KEY (match_id) REFERENCES {$prefix}matches(id) ON DELETE CASCADE,
			FOREIGN KEY (team_id) REFERENCES {$prefix}teams(id) ON DELETE CASCADE
		) $charset_collate;";

		// import dbDelta
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	// This information can be stored in the wp_options table @prashanth
	private static function create_max_team_table(string $prefix) {
		/**
		 * Create the predictions table
		 */

		global $wpdb;
		$table_name = $prefix . 'max_teams';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			max_teams mediumint(9) NOT NULL,
			PRIMARY KEY (id)
		) $charset_collate;";

		// import dbDelta
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}
